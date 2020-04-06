<template>
  <div class="modal is-active">
    <div class="modal-background"></div>
    <div v-if="isLoading" class="modal-content is-loading">Loading photos...</div>
    <div v-else class="modal-content">
      <img
        v-if="currentPhoto"
        :src="'/storage/photos/' + currentPhoto.photo"
        :alt="currentPhoto.title"
        class="photo-fullsize"
        @click.prevent="close"
      />
    </div>
    <div class="timeline">
      <div class="timeline-track"></div>
      <div class="timeline-items">
        <template v-for="photo in photos">
          <div
            :key="'marker' + photo.id"
            class="timeline-marker"
            :class="{
              'is-active': photo.id === currentPhoto.id,
            }"
            :style="{left:datePercentage(photo.date) + '%'}"
            @click.prevent="currentPhoto = photo"
          ></div>
          <div
            :key="'label' + photo.id"
            class="timeline-marker-label"
            :style="{
              left: photoLeft(photo.date),
              right: photoRight(photo.date),
            }"
          >{{ dateString(photo.date) }}</div>
        </template>
      </div>
    </div>
    <button class="modal-close is-large" aria-label="close" @click.prevent="close"></button>
  </div>
</template>

<script>
import { format } from 'date-fns';

export default {
  props: {
    coords: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      isLoading: true,
      isLoading: true,
      photos: [],
      currentPhoto: null,
    };
  },

  created() {
    this.getPhotos();
  },

  computed: {
    dates() {
      return this.photos.map((photo) => photo.date).sort();
    },
    firstDate() {
      if (this.photos.length > 0) {
        return new Date(this.photos[0].date).getTime();
      }
    },
    lastDate() {
      if (this.photos.length > 0) {
        return new Date(this.photos[this.photos.length - 1].date).getTime();
      }
    },
    dateRange() {
      if (this.photos.length > 0) {
        return new Date(this.lastDate).getTime() - new Date(this.firstDate).getTime();
      }
    },
  },

  methods: {
    dateString(date) {
      return format(new Date(date), 'd MMM yyyy');
    },
    datePercentage(date) {
      return (((new Date(date).getTime() - this.firstDate) / this.dateRange) * 100);
    },
    photoLeft(date) {
      let percentage = this.datePercentage(date);

      if (percentage > 50) {
        return 'auto';
      }

      return percentage + '%';
    },
    photoRight(date) {
      let percentage = this.datePercentage(date);

      if (percentage <= 50) {
        return 'auto';
      }

      return (100 - percentage) + '%';
    },
    close() {
      this.$emit('close');
    },
    getPhotos() {
      const url = new URL('/photos', window.location.origin);
      const params = {
        lat: this.coords.lat,
        lng: this.coords.lng,
      };

      Object.keys(params).forEach((key) => url.searchParams.append(key, params[key]));

      this.isLoading = true;

      fetch(url, {
        credentials: 'same-origin',
        method: 'get',
        headers: {
          'Content-Type': 'application/json',
        },
      })
        .then((response) => {
          response.json().then((json) => {
            this.photos = json.photos;

            if (this.photos.length > 0) {
              this.currentPhoto = this.photos[this.photos.length - 1];
            }
          });
        })
        .catch((error) => {
          console.error(error);
          alert("Oh no. Something's broken");
        })
        .then(() => {
          this.isLoading = false;
        });
    },
  },
};
</script>
