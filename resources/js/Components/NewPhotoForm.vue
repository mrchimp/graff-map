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
      </section>
      <footer class="modal-card-foot">
        <div class="field">
          <div class="control">
            <div class="buttons">
              <button
                v-if="page !== 1"
                type="button"
                class="button is-default"
                @click.prevent="page--"
              >Back</button>
              <button
                v-if="page === 4"
                type="submit"
                class="button is-success"
                :disabled="!nextable"
              >{{ nextable ? 'All done!' : 'Fill in details...' }}</button>
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
      pageCount: 4,
      objectUrl: null,
      previewCropped: null,
      title: "",
      artist: "",
      selectedFile: null,
      debouncedUpdatePreview: debounce(this.updatePreview, 257),
      debug: window.debug,
      exifDate: null,
      photoLatLng: null
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
        "Add extra details"
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
      console.log("save");

      const canvas = this.cropper.getCroppedCanvas();

      canvas.toBlob(blob => {
        const formData = new FormData();

        formData.append("my-avatar-file", blob, "avatar.png");

        axios.post("/api/files", formData, {
          headers: {
            "Content-Type": "multipart/form-data"
          }
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
    }
  }
};
</script>
