<template>
    <header class="d-flex align-items-center p-3 bg-light border-bottom">
      <button class="btn btn-outline-primary me-3" @click="goBack">
        <i class="bi bi-arrow-bar-left"></i>
      </button>
      <h1 class="m-0"></h1>
    </header>
  </template>
  
  <script setup>
  import { useRouter } from 'vue-router';
  
  const router = useRouter();
  const goBack = () => {
    router.back();
  };
  </script>
  
  <style scoped>
  header {
    display: flex;
    align-items: center;
    height: 60px;
  }
  </style>