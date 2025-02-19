<script setup lang="ts">
const auth = useAuthStore()

definePageMeta({
  requiresAuth: false
})
</script>

<template>
  <div>
    <Navbar />
    <h1>{{ auth.user?.name }}</h1>
    <RestaurantMenu />
    <Footer />
  </div>
</template>