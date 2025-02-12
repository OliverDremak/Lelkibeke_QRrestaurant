<script setup lang="ts">
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
</script>

<template>
  <div>
    <Navbar />
    <QRreaded/>
    <Footer/>
  </div>
</template>
