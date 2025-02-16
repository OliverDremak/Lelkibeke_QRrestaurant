<template>
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Lelkibéke</a>
      <button class="navbar-toggler" @click="toggleNavbar">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div :class="{'collapse navbar-collapse': !isNavbarOpen, 'navbar-collapse': isNavbarOpen}" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item m-2">
            <ButtonComponet @click="goToRegister" text="Register" class="w-100"/>
          </li>            
          <li class="nav-item m-2">             
            <ButtonComponet @click="goToLogin" text="Login" class="w-100"/>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>
  
  <script setup lang="ts">
  import { useRouter } from 'vue-router' 
  import { ref } from 'vue';
import ButtonComponet from './ButtonComponet.vue';

const isNavbarOpen = ref(false);
const toggleNavbar = () => {
  isNavbarOpen.value = !isNavbarOpen.value;
};

  
  const router = useRouter()
  // TODO: Show if the user is logged in
  const goToLogin = () => {
    router.push('/login')
  }
  const goToRegister = () => {
    router.push('/register')
  }
  </script>
  
  <style scoped>
  .navbar {
    margin-bottom: 20px;
  }
  </style>