<template>
  <div class="graff-map">
    <l-map ref="myMap" :zoom="zoom" :center="center" @click="onClickMap">
      <l-tile-layer :url="url" :attribution="attribution" />
    </l-map>
    <div class="modal" :class="{'is-active': showAddWallModal}">
      <div class="modal-background"></div>
      <new-photo-form :lat-lng="clickLatLng" @close="showAddWallModal = false" />
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
      url: "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
      attribution:
        '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors',
      center: latLng(51.457526, -2.593806),
      zoom: 13,
      clickLatLng: null,
      showAddWallModal: false,
      newWallName: ""
    };
  },
  components: {
    LMap,
    LTileLayer,
    NewPhotoForm
  },
  methods: {
    updateZoom(zoom) {
      this.zoom = zoom;
    },
    updateCenter(center) {
      this.center = center;
    },
    onClickMap(e) {
      this.clickLatLng = e.latlng;
      this.showAddWallModal = true;
    }
  }
};
</script>
