<script setup lang="ts">
import { useRoute, useRouter } from 'vue-router';
const auth = useAuthStore()

definePageMeta({
  requiresAuth: true
})

const route = useRoute();
const router = useRouter();
</script>

<template>
  <div>
      <h1>Welcome {{ auth.user?.name }}</h1>
      <button @click="auth.logout">Logout</button>
    </div>
  <div>
    <Navbar />
    <QRreaded/>
    <Footer/>
  </div>
</template>
