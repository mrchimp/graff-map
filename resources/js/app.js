require("./bootstrap");

import Vue from "vue";
import GraffMap from "./Components/GraffMap.vue";

window.debug = false;

new Vue({
  el: "#app",
  components: {
    GraffMap
  }
});
