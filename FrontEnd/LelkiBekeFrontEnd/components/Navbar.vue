<template>
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Lelkibéke</a>
      <button class="navbar-toggler" @click="toggleNavbar">
        <span class="navbar-toggler-icon"></span>
      </button>
      <transition name="fade">
        <div v-show="isNavbarOpen" class="navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto">
            <!-- Show these buttons only when user is NOT logged in -->
            <template v-if="!auth.token">
              <li class="nav-item m-2 text-center">
                <ButtonComponet @click="goToRegister" text="Register" class="w-100"/>
              </li>            
              <li class="nav-item m-2 text-center">             
                <ButtonComponet @click="goToLogin" text="Login" class="w-100"/>
              </li>
            </template>
            <!-- Show logout when user is logged in -->
            <template v-else>
              <li class="nav-item m-2 text-center">
                <span class="me-3">Welcome, {{ auth.user?.name }}</span>
                <ButtonComponet @click="handleLogout" text="Logout" class="w-100"/>
              </li>
            </template>
          </ul>
        </div>
      </transition>
    </div>
  </nav>
</template>
  
<script setup lang="ts">
import { useRouter } from 'vue-router' 
import { ref } from 'vue';
import ButtonComponet from './ButtonComponet.vue';
import { useAuthStore } from '~/stores/auth';

const auth = useAuthStore();
const router = useRouter();
const isNavbarOpen = ref(false);

const toggleNavbar = () => {
  isNavbarOpen.value = !isNavbarOpen.value;
};

const goToLogin = () => {
  router.push('/auth')
};

const goToRegister = () => {
  router.push('/auth?register=true')
};

const handleLogout = async () => {
  await auth.logout();
  router.push('/auth');
};
</script>
  
<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.5s ease-in-out;
}
.fade-leave-to {
  opacity: 0;
}
.fade-enter-to {
  opacity: 1;
}
  </style>