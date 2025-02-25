<template>
  <nav class="custom-navbar">
    <div class="container">
      <div class="left-section">
        <button v-if="route.path === '/profile'" 
                @click="goBack" 
                class="nav-button back-button">
          <i class="bi bi-arrow-left"></i> Back
        </button>
        <a class="navbar-brand" href="#">
          <span class="brand-text">Lelkibéke</span>
        </a>
      </div>
      <button class="navbar-toggler" @click="toggleNavbar" :class="{ 'is-active': isNavbarOpen }">
        <span></span>
        <span></span>
        <span></span>
      </button>
      <div class="navbar-links" :class="{ 'show': isNavbarOpen }">
        <ul class="nav-list">
          <!-- Show these buttons only when user is NOT logged in -->
          <template v-if="!auth.token">
            <li class="nav-item">
              <button @click="goToRegister" class="nav-button register">Register</button>
            </li>            
            <li class="nav-item">             
              <button @click="goToLogin" class="nav-button login">Login</button>
            </li>
          </template>
          <!-- Show profile and logout when user is logged in -->
          <template v-else>
            <li class="nav-item user-menu">
              <div class="welcome-text">Welcome, {{ auth.user?.name }}</div>
              <button @click="goToProfile" class="nav-button profile">Profile</button>
              <button @click="handleLogout" class="nav-button logout">Logout</button>
            </li>
          </template>
        </ul>
      </div>
      <transition name="fade">
        <div v-show="isNavbarOpen" class="navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto">
            <!-- Show these buttons only when user is NOT logged in -->
             <li class="nav-item m-2 text-center">
                <DarkModeToggle />
             </li>
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
import { useRouter, useRoute } from 'vue-router' 
import { ref } from 'vue';
import ButtonComponet from './ButtonComponet.vue';
import { useAuthStore } from '~/stores/auth';

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();
const isNavbarOpen = ref(false);

const toggleNavbar = () => {
  isNavbarOpen.value = !isNavbarOpen.value;
};

const goToLogin = () => {
  const currentPath = route.fullPath;
  router.push(`/auth?redirect=${encodeURIComponent(currentPath)}`);
};

const goToRegister = () => {
  const currentPath = route.fullPath;
  router.push(`/auth?register=true&redirect=${encodeURIComponent(currentPath)}`);
};

const goToProfile = () => {
  router.push('/profile');
};

const handleLogout = async () => {
  const isProfilePage = route.path === '/profile';
  await auth.logout();
  router.push(isProfilePage ? '/' : '/auth');
};

const goBack = () => {
  router.back();
};
</script>
  
<style scoped>
.custom-navbar {
  background: linear-gradient(to right, #ffffff, #f8f9fa);
  padding: 1rem 0;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 1000;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.navbar-brand {
  text-decoration: none;
}

.brand-text {
  font-size: 1.8rem;
  font-weight: 700;
  background: linear-gradient(45deg, #dd6013, #ffbd00);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
  transition: transform 0.3s ease;
}

.navbar-brand:hover .brand-text {
  transform: scale(1.05);
}

.navbar-toggler {
  display: none;
  flex-direction: column;
  justify-content: space-between;
  width: 30px;
  height: 21px;
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 0;
  z-index: 10;
}

.navbar-toggler span {
  width: 100%;
  height: 3px;
  background-color: #dd6013;
  border-radius: 10px;
  transition: all 0.3s linear;
  position: relative;
  transform-origin: 1px;
}

.navbar-toggler.is-active span:first-child {
  transform: rotate(45deg);
}

.navbar-toggler.is-active span:nth-child(2) {
  opacity: 0;
}

.navbar-toggler.is-active span:last-child {
  transform: rotate(-45deg);
}

.navbar-links {
  display: flex;
  align-items: center;
}

.nav-list {
  display: flex;
  gap: 1rem;
  list-style: none;
  margin: 0;
  padding: 0;
}

.nav-button {
  padding: 0.5rem 1.5rem;
  border-radius: 25px;
  font-weight: 600;
  transition: all 0.3s ease;
  cursor: pointer;
  border: none;
  margin: 0 0.5rem;
}

.nav-button:hover {
  transform: translateY(-2px);
}

.register {
  background: linear-gradient(45deg, #dd6013, #ffbd00);
  color: white;
}

.register:hover {
  box-shadow: 0 2px 8px rgba(221, 96, 19, 0.4);
}

.login {
  background: white;
  color: #dd6013;
  border: 2px solid #dd6013;
}

.login:hover {
  background: #dd6013;
  color: white;
}

.profile {
  background: #dd6013;
  color: white;
  border: none;
}

.logout {
  background: #f8f9fa;
  color: #dc3545;
  border: 2px solid #dc3545;
}

.welcome-text {
  margin-right: 1rem;
  color: #333;
  font-weight: 500;
}

.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.3s ease;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateY(-20px);
  opacity: 0;
}

.left-section {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.back-button {
  background: transparent;
  color: #dd6013;
  border: 1px solid #dd6013;
  padding: 0.5rem 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.back-button:hover {
  background: #dd6013;
  color: white;
}

@media (max-width: 768px) {
  .navbar-toggler {
    display: flex;
  }

  .navbar-links {
    display: none;
    width: 100%;
    background: white;
    padding: 1rem;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  }

  .navbar-links.show {
    display: block;
  }

  .nav-list {
    flex-direction: column;
    align-items: center;
    width: 100%;
  }

  .nav-item {
    width: 100%;
    margin: 0.5rem 0;
  }

  .nav-button {
    width: 100%;
    margin: 0.5rem 0;
  }
}
</style>