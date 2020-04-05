<template>
  <div class="timeline-marker" @mouseover="onMouseOver" @mouseout="onMouseOut"></div>
</template>

<script>
export default {
  data() {
    return {
      hovering: false,
    };
  },
  methods: {
    onMouseOver() {
      this.hovering = true;
    },
    onMouseOut() {
      this.hovering = false;
    },
  },
};
</script>
