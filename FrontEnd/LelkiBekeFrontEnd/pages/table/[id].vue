<template>
  <div>
    <h1>Table {{ tableId }}</h1>
  </div>
</template>

<script setup>
import { useRoute } from 'vue-router';

const route = useRoute();
const tableId = route.params.id;
</script>