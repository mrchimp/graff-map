<template>
  <div class="modal-card">
    <form @submit.prevent="onSubmit">
      <header class="modal-card-head">
        <p class="modal-card-title">{{ pageTitle }} ({{ page }} / {{ pageCount }})</p>
        <button class="delete" aria-label="close" @click.prevent="close"></button>
      </header>
      <section class="modal-card-body">
        <div v-show="page === 1">
          <div class="file">
            <label class="file-label">
              <input
                class="file-input"
                type="file"
                name="resume"
                accept="image/png, image/jpeg"
                placeholder="Select an image"
                @input="pickFile"
              />

              <span class="file-cta">
                <span class="file-icon">
                  <i class="fas fa-upload"></i>
                </span>
                <span class="file-label">Choose a file…</span>
              </span>
            </label>
          </div>
        </div>
        <div v-show="page === 2">
          <div class="image-preview-wrapper">
            <img
              class="image-preview"
              ref="source"
              :src="objectUrl"
              alt="Image preview"
              v-if="objectUrl"
            />
          </div>
          <div class="image-preview-wrapper" v-if="objectUrl && debug">
            <img class="image-preview" :src="previewCropped" alt="Cropped image preview" />
          </div>
        </div>
        <div v-if="page === 3">
          <div class="field" v-if="debug">
            <label class="label">Coordinates</label>
            <div class="control">
              <input class="input" type="text" disabled v-model="photoLatLng" />
            </div>
          </div>

          <div class="field">
            <coord-selector
              v-if="photoLatLng !== null"
              :coords="photoLatLng"
              @input="updateLatLng"
            />
          </div>
        </div>

        <div v-show="page === 4">
          <div class="field">
            <label for="photoTitle" class="label">Title</label>
            <div class="control">
              <input
                type="text"
                class="input"
                id="photoTitle"
                v-model="title"
                placeholder="What's this piece called?"
                ref="title"
              />
            </div>
          </div>

          <div class="field">
            <label for="photoArtist" class="label">Artist</label>
            <div class="control">
              <input
                type="text"
                class="input"
                id="photoArtist"
                v-model="artist"
                placeholder="Who made this piece?"
              />
            </div>
          </div>
        </div>

        <div v-show="page === 5">
          <p>
            Thanks for submitting that. We'll check it's not spam and then add it to the map.
          </p>
        </div>
      </section>
      <footer class="modal-card-foot">
        <div class="field">
          <div class="control">
            <div class="buttons">
              <button
                v-if="page !== 1 && page !== this.lastPage"
                type="button"
                class="button is-default"
                @click.prevent="page--"
              >Back</button>
              <button
                v-if="page === this.submitPage"
                type="submit"
                class="button is-success"
                :class="{'is-loading': saving}"
                :disabled="!nextable||saving"
              >{{ nextable ? 'All done!' : 'Fill in details...' }}</button>
              <button
                v-else-if="page === this.lastPage"
                type="button"
                class="button is-success"
                @click.prevent="finish"
              >Done</button>
              <button
                v-else-if="page !== 1 || this.nextable"
                type="button"
                class="button is-success"
                @click.prevent="page++"
                :disabled="!nextable"
              >Next</button>
            </div>
          </div>
        </div>
      </footer>
    </form>
  </div>
</template>

<script>
import Cropper from "cropperjs";
import debounce from "lodash/debounce";
import get from "lodash/get";
import ExifReader from "exifreader";
import { latLng } from "leaflet";
import CoordSelector from "./CoordSelector.vue";

export default {
  components: {
    CoordSelector
  },
  data() {
    return {
      page: 1,
      pageCount: 5,
      objectUrl: null,
      previewCropped: null,
      title: "",
      artist: "",
      selectedFile: null,
      debouncedUpdatePreview: debounce(this.updatePreview, 257),
      debug: window.debug,
      exifDate: null,
      photoLatLng: null,
      saving: false,
      lastPage: 5,
      submitPage: 4,
    };
  },
  computed: {
    finalLatLng() {
      if (this.photoLatLng) {
        return this.photoLatLng;
      }

      // Default to Null Island
      return latLng(0, 0);
    },
    pageTitle() {
      return [
        "Choose an image",
        "Crop your image",
        "Set photo coordinates",
        "Add extra details",
        'All done',
      ][this.page - 1];
    },
    nextable() {
      switch (this.page) {
        case 1:
          return !!this.objectUrl;
        case 2:
          return true;
        case 3:
          return (
            this.photoLatLng !== null &&
            !(this.photoLatLng.lat === 0 && this.photoLatLng.lng === 0)
          );
        case 4:
          return this.title && this.artist;
        case 5:
          return true;
        default:
          console.error("Invalid page.");
          return false;
      }
    }
  },
  methods: {
    close() {
      this.$emit("close");
    },
    pickFile(e) {
      if (e.target.files.length === 0) {
        console.error("No file!");
        return;
      }

      const selectedFile = e.target.files[0];

      this.getExif(selectedFile);

      if (this.cropper) {
        this.cropper.destroy();
      }

      if (this.objectUrl) {
        window.URL.revokeObjectURL(this.objectUrl);
      }

      if (!selectedFile) {
        this.cropper = null;
        this.objectUrl = null;
        this.previewCropped = null;
        return;
      }

      this.objectUrl = window.URL.createObjectURL(selectedFile);
      this.$nextTick(this.setupCropperInstance);

      this.page = 2;
    },
    setupCropperInstance() {
      this.cropper = new Cropper(this.$refs.source, {
        autoCropArea: 1,
        crop: this.debouncedUpdatePreview,
        viewMode: 1,
        background: false,
        zoomable: false
      });
    },
    updatePreview(e) {
      const canvas = this.cropper.getCroppedCanvas();
      this.previewCropped = canvas.toDataURL("image/jpeg");
    },
    onSubmit() {
      this.saving = true;

      const canvas = this.cropper.getCroppedCanvas();

      canvas.toBlob(blob => {
        const formData = new FormData();

        formData.append("photo", blob, "photo.jpg");
        formData.append('title', this.title);
        formData.append('artist', this.artist);
        formData.append('lat', this.photoLatLng.lat);
        formData.append('lng', this.photoLatLng.lng);

        fetch("/photos", {
          body: formData,
          credentials: 'same-origin',
          method: 'post',
          headers: {
            "X-CSRF-Token": document.head.querySelector("[name~=csrf-token][content]").content
          }
        })
        .then(response=> {
          if (response.status !== 201) {
            throw 'Bad response status.';
          }

          this.page++;
        })
        .catch(error => {
          console.error(error);
          alert('There was a problem');
        })
        .then(() => {
          this.saving = false;
        });
      });
    },
    getExif(file) {
      this.updateLatLng(null);

      file.arrayBuffer().then(fileArrayBuffer => {
        const tags = ExifReader.load(fileArrayBuffer);
        let lat = get(tags, "GPSLatitude.description");
        let lng = get(tags, "GPSLongitude.description");

        if (get(tags, 'GPSLatitudeRef.descripiton') === 'South latitude') {
          lat = -lat;
        }

        if (get(tags, 'GPSLongitudeRef.description') === 'West longitude') {
          lng = -lng;
        }

        if (lat && lng) {
          this.updateLatLng(latLng(lat, lng));
        } else {
          this.updateLatLng(latLng(0, 0));
        }

        this.exifDate = get(tags, "DateTimeOriginal.value.0", null);
      });
    },
    updateLatLng(latLng) {
      this.photoLatLng = latLng;
    },
    finish() {
      this.page = 1;
      this.objectUrl = null;
      this.previewCropped = null;
      this.title = '';
      this.artist = '';
      this.selectedFile = null;
      this.exifDate = null;
      this.photoLatLng = null;
      this.$emit('close');
    },
  }
};
</script>
