<template>
  <div>
    <Navbar />
    <h1>Table {{ tableId }}</h1>
    <RestaurantMenu />
    <Footer/>
  </div>
</template>

<script setup>
import { useRoute } from 'vue-router';
import { useFetch } from '#app'; 
import Echo from 'laravel-echo';



const handleQrcodescanned = (tableId) => {

  console.log('Qr code scanned', tableId);
};

// const { data: table, error, refresh } = useFetch(`/api/tables/${tableId}`);

import { onMounted } from 'vue';
import axios from 'axios';

onMounted(() => {
  try {
    const route = useRoute();
    const tableId = route.params.id;
    console.log('Table id:', tableId);

    axios.post(`http://localhost:8000/api/table-scanned/${tableId}`)
      .then(() => {
      console.log('Table scanned successfully ', tableId);
      })
      .catch(error => {
      console.error('Error scanning table', error);
      });
  } catch (error) {
    console.error('Unexpected error', error);
  }
});


</script>