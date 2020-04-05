<template>
  <div class="graff-map">
    <l-map ref="map" :zoom="zoom" :center="center" @ready="getPoints" @update:bounds="getPoints">
      <l-tile-layer :url="url" :attribution="attribution" />
      <l-marker
        v-for="point in points"
        :key="String(point.lat) + String(point.lng)"
        :lat-lng="point"
        :icon="icon"
        @click="showPhotoModal(point)"
      />
    </l-map>
    <new-photo-modal
      v-if="showAddPhotoModal"
      :lat-lng="clickLatLng"
      @close="showAddPhotoModal = null"
    />
    <div class="field is-grouped map-controls">
      <p class="control">
        <button class="button is-primary" @click.prevent="showAddPhotoModal = true">
          <span class="icon">
            <i class="fas fa-plus"></i>
          </span>
          <span>Add New</span>
        </button>
      </p>
    </div>
    <photo-modal v-if="modalCoords" :coords="modalCoords" @close="modalCoords = null" />
  </div>
</template>

<script>
import { latLng, icon } from 'leaflet';
import { LMap, LTileLayer, LMarker } from 'vue2-leaflet';
import NewPhotoModal from './NewPhotoModal.vue';
import PhotoModal from './PhotoModal.vue';

export default {
  data() {
    return {
      url: 'https://tiles.stadiamaps.com/tiles/alidade_smooth/{z}/{x}/{y}{r}.png',
      attribution:
        '&copy; <a href="https://stadiamaps.com/">Stadia Maps</a>, &copy; <a href="https://openmaptiles.org/">OpenMapTiles</a> &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors',
      center: latLng(51.457526, -2.593806),
      zoom: 13,
      clickLatLng: null,
      showAddPhotoModal: false,
      newWallName: '',
      points: [],
      icon: icon({
        iconUrl: '/images/marker-icon.png',
        iconRetinaUrl: '/images/marker-icon-2x.png',
        shadowUrl: '/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
      }),
      modalCoords: null,
    };
  },
  components: {
    LMap,
    LTileLayer,
    LMarker,
    NewPhotoModal,
    PhotoModal,
  },
  methods: {
    onClickMap(e) {
      this.clickLatLng = e.latlng;
      this.showAddPhotoModal = true;
    },
    getPoints() {
      const url = new URL('/points', window.location.origin);
      const bounds = this.$refs.map.mapObject.getBounds();
      const params = {
        north: bounds.getNorth(),
        south: bounds.getSouth(),
        east: bounds.getEast(),
        west: bounds.getWest(),
      };
      Object.keys(params).forEach((key) => url.searchParams.append(key, params[key]));

      fetch(url, {
        method: 'get',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
        },
      })
        .then((response) => {
          response.json().then((json) => {
            this.points = json.points;
          });
        })
        .catch((error) => {
          console.error(error);
          alert("Oh no. Something's broken.");
        });
    },
    showPhotoModal(point) {
      this.modalCoords = point;
    },
  },
};
</script>
