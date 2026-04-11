<script setup>
defineProps({
  listing: {
    required: true,
  },
});

</script>

<template>
  <div class="listing listingcard col-span-12 sm:col-span-6" v-bind="listing.attributes">
    <component :is="listing.element" :href="listing.href">
      <div class="listing__img">
        <img :src="listing.img.src" loading="lazy">
        <div v-if="listing.labels.length" class="labels">
          <div v-for="label in listing.labels" :class="'theme-custom-background theme-custom-color label ' + label.type" v-html="label.content"></div>
        </div>
      </div>
      <div class="listing__data">
        <div>
          <div class="font-bold uppercase">
            {{ listing.type ? listing.type + ': ' + listing.address : listing.address }}
          </div>
          <div class="uppercase">{{ listing.locationString }}</div>
        </div>
        <div>
          <div class="flex -mx-1">
            <span class="mx-1" v-for="fact in listing.facts">{{ fact }}</span>
          </div>
        </div>
      </div>
    </component>
  </div>
</template>
