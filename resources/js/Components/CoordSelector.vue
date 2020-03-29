<template>
  <div class="modal-map">
    <l-map ref="map" :zoom="zoom" :center="center" @click="onClickMap">
      <l-tile-layer :url="url" :attribution="attribution" />
      <l-marker :lat-lng="coords" :icon="icon" />
    </l-map>
  </div>
</template>

<script>
import { latLng, icon } from "leaflet";
import { LIcon, LMap, LTileLayer, LMarker } from "vue2-leaflet";

export default {
  components: {
    LMap,
    LTileLayer,
    LMarker
  },
  props: {
    coords: {
      type: Object,
      default: latLng(0, 0)
    }
  },
  data() {
    return {
      url:
        "https://tiles.stadiamaps.com/tiles/alidade_smooth/{z}/{x}/{y}{r}.png",
      attribution:
        '&copy; <a href="https://stadiamaps.com/">Stadia Maps</a>, &copy; <a href="https://openmaptiles.org/">OpenMapTiles</a> &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors',
      center: latLng(0, 0),
      zoom: 15,
      icon: icon({
        iconUrl: "/images/marker-icon.png",
        iconRetinaUrl: "/images/marker-icon-2x.png",
        shadowUrl: "/images/marker-shadow.png",
        iconSize: [25, 41],
        iconAnchor: [12, 41]
      })
    };
  },
  created() {
    this.center = this.coords;
  },
  methods: {
    onClickMap(e) {
      this.markerlatLng = e.latlng;
      this.$emit("input", e.latlng);
    }
  }
};
</script>
