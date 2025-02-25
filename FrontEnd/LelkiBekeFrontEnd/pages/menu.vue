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
    <PopularItems />
    <RestaurantMenu />
    <Footer />
  </div>
</template>