<script setup lang="ts">
import { useRoute, useRouter } from 'vue-router';
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
    <PopularItems />
    <QRreaded/>
    <Footer/>
  </div>
</template>
