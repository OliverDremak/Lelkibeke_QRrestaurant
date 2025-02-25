<script setup lang="ts">
import { useRoute, useRouter } from 'vue-router';
import PopularItems from '~/components/PopularItems.vue';
const auth = useAuthStore()

definePageMeta({
  requiresAuth: false
})

const route = useRoute();
const router = useRouter();
</script>

<template>
  <div>
    <Navbar />
    <div class="bg">
      <PopularItems />
      <QRreaded/>
    </div>
    <Footer/>
  </div>
</template>

<style scoped>
    .bg{
      background: linear-gradient(rgba(255, 255, 255, 0.5), rgba(0, 0, 0, 0.5));
    }
</style>
