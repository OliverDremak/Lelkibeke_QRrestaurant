<template>
  <div>
    <Navbar />
    <h1>Table {{ tableId }}</h1>
    <RestaurantMenu />
    <Footer />
  </div>
</template>

<script setup>
import { useRoute } from 'vue-router';
import { useFetch } from '#app'; 
import axios from 'axios';
import { onMounted } from 'vue';

const route = useRoute();
const tableId = route.params.id;

const { data: table, error, refresh } = useFetch(`/api/tables/${tableId}`);

const setOccupancy = async (status) => {
  try {
    await $fetch(`/api/setOccupancyStatus/${tableId}/${status ? 1 : 0}`, {
      method: 'POST',
    });
    // Refresh the table data to reflect the new occupancy status
    refresh();
  } catch (err) {
    console.error('Error setting occupancy status:', err);
  }
}

if (error.value) {
  console.error('Error fetching table data:', error.value);
}

onMounted(async () => {
  try {
    await axios.post(`http://localhost:8000/api/table-scanned/${tableId}`);
  } catch (err) {
    console.error('Error posting table scanned:', err);
  }
});
</script>