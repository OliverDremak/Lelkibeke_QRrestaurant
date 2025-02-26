<script setup>
import { useAuthStore } from '~/stores/auth';

// Initialize auth store at app startup
const auth = useAuthStore();

// Check authentication status when app starts
onMounted(() => {
  auth.checkAuth();
})
</script>

<script>
import { NuxtPage } from '#components';
import RestaurantMenu from './components/RestaurantMenu.vue';

</script>



<template>
  <html>
    <body class="body">
      <NuxtPage/>
    </body>
  </html>
</template>

<style scoped>
@import url("~/assets/css/main.css");
</style>