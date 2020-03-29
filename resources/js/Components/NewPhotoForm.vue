<template>
  <div class="modal-card">
    <header class="modal-card-head">
      <p class="modal-card-title">Add a Piece</p>
      <button class="delete" aria-label="close" @click.prevent="close"></button>
    </header>
    <section class="modal-card-body">
      <form @submit.prevent="onSubmit">
        <div class="file">
          <label class="file-label">
            <input
              class="file-input"
              type="file"
              name="resume"
              accept="image/png, image/jpeg"
              placeholder="Select an image"
              @input="setupCropper"
            />

            <span class="file-cta">
              <span class="file-icon">
                <i class="fas fa-upload"></i>
              </span>
              <span class="file-label">Choose a file…</span>
            </span>
          </label>
        </div>

        <div>
          <div>
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
        </div>

        <div v-if="objectUrl">
          <div class="field" v-if="debug">
            <label class="label">Coordinates</label>
            <div class="control">
              <input class="input" type="text" disabled v-model="latLng" />
            </div>
          </div>

          <div class="field">
            <coord-selector
              v-if="photoLatLng !== null"
              :coords="photoLatLng"
              @input="updateLatLng"
            />
          </div>

          <div class="field">
            <label for="photoTitle" class="label">Title</label>
            <div class="control">
              <input type="text" class="input" id="photoTitle" v-model="title" />
            </div>
          </div>

          <div class="field">
            <label for="photoArtist" class="label">Artist</label>
            <div class="control">
              <input type="text" class="input" id="photoArtist" v-model="artist" />
            </div>
          </div>

          <div class="field">
            <div class="control">
              <button type="submit" class="button is-success" :disabled="!submittable">Save changes</button>
              <button class="button" @click.prevent="close">Cancel</button>
            </div>
          </div>
        </div>
      </form>
    </section>
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
    submittable() {
      return (
        !!this.objectUrl &&
        !!this.photoLatLng &&
        !(this.photoLatLng.lat === 0 && this.photoLatLng.lng === 0)
      );
    }
  },
  methods: {
    close() {
      this.$emit("close");
    },
    onSubmit() {
      console.log("save");
    },
    setupCropper(e) {
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
      const canvas = this.cropper.getCroppedCanvas();

      canvas.toBlob(blob => {
        const formData = new FormData();

        formData.append("my-avatar-file", blob, "avatar.png");

        this.$axios.post("/api/files", formData, {
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
        const lat = get(tags, "GPSLatitude.description");
        const lng = get(tags, "GPSLongitude.description");

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
