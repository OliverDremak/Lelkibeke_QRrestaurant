<template>
  <div>
    <Navbar />
    <h1>Table {{ tableId }}</h1>
    <RestaurantMenu :table-id="tableId"/>
    <Footer/>
  </div>
</template>

<script setup>
import { useRoute } from 'vue-router';
import { useFetch } from '#app'; 
import axios from 'axios';
import { onMounted } from 'vue';

const route = useRoute();
const tableId = parseInt(route.params.id,10);
console.log(tableId);

onMounted(async () => {
  try {
    await axios.post(`http://localhost:8000/api/table-scanned/${tableId}`);
  } catch (err) {
    console.error('Error posting table scanned:', err);
  }
});
</script>