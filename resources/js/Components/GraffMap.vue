<template>
  <div class="graff-map">
    <l-map ref="map" :zoom="zoom" :center="center" @ready="getPoints" @update:bounds="getPoints">
      <l-tile-layer :url="url" :attribution="attribution" />
      <l-marker
        v-for="point in points"
        :key="String(point.coordinates[0]) + String(point.coordinates[1])"
        :lat-lng="makeLatLng(point)"
        :icon="icon"
      />
    </l-map>
    <div class="modal" :class="{ 'is-active': showAddPieceModal }">
      <div class="modal-background"></div>
      <new-photo-form :lat-lng="clickLatLng" @close="showAddPieceModal = false" />
    </div>
    <div class="field is-grouped map-controls">
      <p class="control">
        <button class="button is-primary" @click.prevent="showAddPieceModal = true">
          <span class="icon">
            <i class="fas fa-plus"></i>
          </span>
          <span>Add New</span>
        </button>
      </p>
    </div>
  </div>
</template>

<script>
import { latLng, icon } from 'leaflet';
import { LMap, LTileLayer, LMarker } from 'vue2-leaflet';
import NewPhotoForm from './NewPhotoForm.vue';

export default {
  data() {
    return {
      url: 'https://tiles.stadiamaps.com/tiles/alidade_smooth/{z}/{x}/{y}{r}.png',
      attribution:
        '&copy; <a href="https://stadiamaps.com/">Stadia Maps</a>, &copy; <a href="https://openmaptiles.org/">OpenMapTiles</a> &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors',
      center: latLng(51.457526, -2.593806),
      zoom: 13,
      clickLatLng: null,
      showAddPieceModal: false,
      newWallName: '',
      points: [],
      icon: icon({
        iconUrl: '/images/marker-icon.png',
        iconRetinaUrl: '/images/marker-icon-2x.png',
        shadowUrl: '/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
      }),
    };
  },
  components: {
    LMap,
    LTileLayer,
    LMarker,
    NewPhotoForm,
  },
  methods: {
    onClickMap(e) {
      this.clickLatLng = e.latlng;
      this.showAddPieceModal = true;
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
    makeLatLng(coords) {
      return latLng(coords.coordinates[1], coords.coordinates[0]);
    },
  },
};
</script>
