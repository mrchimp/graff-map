<template>
  <div class="graff-map">
    <l-map ref="map" :zoom="zoom" :center="center">
      <l-tile-layer :url="url" :attribution="attribution" />
    </l-map>
    <div class="modal" :class="{'is-active': showAddPieceModal}">
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
import { latLng } from "leaflet";
import { LMap, LTileLayer } from "vue2-leaflet";
import NewPhotoForm from "./NewPhotoForm.vue";

export default {
  data() {
    return {
      url:
        "https://tiles.stadiamaps.com/tiles/alidade_smooth/{z}/{x}/{y}{r}.png",
      attribution:
        '&copy; <a href="https://stadiamaps.com/">Stadia Maps</a>, &copy; <a href="https://openmaptiles.org/">OpenMapTiles</a> &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors',
      center: latLng(51.457526, -2.593806),
      zoom: 13,
      clickLatLng: null,
      showAddPieceModal: false,
      newWallName: ""
    };
  },
  components: {
    LMap,
    LTileLayer,
    NewPhotoForm
  },
  methods: {
    onClickMap(e) {
      this.clickLatLng = e.latlng;
      this.showAddPieceModal = true;
    }
  }
};
</script>
