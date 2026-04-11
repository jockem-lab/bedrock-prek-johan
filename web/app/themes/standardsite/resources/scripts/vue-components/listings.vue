<script setup>
import { onMounted, ref } from "vue";
import _ from "lodash";
import Listingcard from "@scripts/vue-components/listingcard.vue";
const filename = 'listings';
const listingItems = ref([]);
const props = defineProps({
  count: {
    type: Number,
    default() {
      return -1;
    },
  },
  sold: {
    type: Number,
    default() {
      return -1;
    },
  },
});

onMounted(() => {
  fetch(prekApiSettings.uploads_path + "/" + filename + prekApiSettings._fasad_lastsync + ".json", {}).then((response) => {
    return response.json();
  }).then((data) => {
    listingItems.value = data;
    if (props.sold === 1 || props.sold === 0) {
      listingItems.value = _.filter(listingItems.value, (item) => {
        return item.sold === props.sold;
      });
    }
    listingItems.value = _.orderBy(listingItems.value, ["sort", "sortPublished"], ["asc", "desc"]);
    if (props.count > 0) {
      listingItems.value = _.take(listingItems.value, props.count);
    }
  });
});
</script>

<template>
  <div class="row !gap-x-5 !gap-y-9">
    <listingcard v-for="listing in listingItems" :listing="listing"></listingcard>
  </div>
</template>