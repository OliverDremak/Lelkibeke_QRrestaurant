<script setup lang="ts">
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

const goToLogin = () => {
  router.push('/login');
};
</script>

<template>
  <div>
    <Navbar />
    <RestaurantMenu />
    <Footer />
  </div>
</template>