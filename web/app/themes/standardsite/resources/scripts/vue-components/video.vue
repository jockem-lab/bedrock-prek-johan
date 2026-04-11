<script setup>
import {onMounted, ref} from "vue";

const debounceTimeout = ref(null);
const video = ref(null);
const attributes = ref({});
const options = ref({
  breakpoints: {
    default: {
      src: "",
    },
  },
});
// const debouncedWidth = ref(0);
const props = defineProps({
  sources: {
    required: true,
  },
  poster: {
    type: String,
    default() {
      return "";
    },
  },
  attributes: {
    type: String,
    default() {
      return "";
    },
  },
});

function resizer() {
  if (debounceTimeout.value) {
    clearTimeout(debounceTimeout.value);
  }
  debounceTimeout.value = setTimeout(() => {
    responsiveVideo();
  }, 500);
}

function responsiveVideo() {
  const widthNow = video.value.getAttribute("data-width-now") || null;
  const maxBreakpoint = Math.max.apply(Math, Object.keys(options.value.breakpoints).filter(key => key <= document.body.clientWidth).map(Number));
  const nowBreakpoint = (maxBreakpoint && maxBreakpoint !== -Infinity) ? maxBreakpoint : "default";
  if (widthNow && widthNow === nowBreakpoint) {
    return; // check if the video needs to be changed
  }
  //new breakpoint, update src
  video.value.setAttribute("data-width-now", nowBreakpoint);
  video.value.setAttribute("src", options.value.breakpoints[nowBreakpoint].src);
  if (video.value.getAttribute("src") !== "") {
    video.value.play();
  }
}

onMounted(() => {
  const componentAttributes = props.attributes.split(' ');
  const defaultAttributes = [
      'autoplay',
      'muted',
      'loop',
      'playsinline'
  ].concat(componentAttributes);
  defaultAttributes.forEach((defaultAttribute) => {
    attributes.value[defaultAttribute] = '';
  })
  //populate breakpoints from sources
  video.value.querySelectorAll("[data-src]").forEach(
      element => options.value.breakpoints[element.getAttribute("data-mw")] = {src: element.getAttribute("data-src")},
  );
  video.value.innerHTML = "";
  window.addEventListener("resize", resizer);
  resizer();
});
</script>

<template>
  <video
      ref="video"
      :poster="poster"
      v-bind="attributes"
  >
    <source v-for="(src, pixel) in sources" :data-src="src" :data-mw="pixel">
    Sorry, your browser doesn't support embedded videos.
  </video>
</template>