<template>
  <nav class="custom-navbar">
    <div class="container">
      <div class="left-section">
        <button v-if="route.path === '/profile'" 
                @click="goBack" 
                class="nav-button back-button">
          <i class="bi bi-arrow-left"></i>{{ t('navbar.back') }}
        </button>
        <NuxtLink to="/" class="navbar-brand">
          <span class="brand-text">{{ t('navbar.title') }}</span>
        </NuxtLink>
      </div>
      <button class="navbar-toggler" @click="toggleNavbar" :class="{ 'is-active': isNavbarOpen }">
        <span></span>
        <span></span>
        <span></span>
      </button>

      <!-- Mobile Menu Overlay -->
      <transition name="fade">
        <div v-show="isNavbarOpen" class="mobile-menu-overlay" @click="closeNavbar"></div>
      </transition>

      <!-- Mobile Menu -->
      <transition name="slide">
        <div v-show="isNavbarOpen" class="mobile-menu">
          <div class="mobile-menu-header">
            <span class="mobile-menu-title">Menu</span>
            <button class="close-button" @click="closeNavbar"></button>
          </div>
          <ul class="mobile-nav-list">
            <li class="mobile-nav-item">
              <DarkModeToggle />
            </li>
            <template v-if="!auth.token">
              <li class="mobile-nav-item">
                <button @click="goToRegister" class="nav-button register w-100">Register</button>
              </li>            
              <li class="mobile-nav-item">             
                <button @click="goToLogin" class="nav-button login w-100">Login</button>
              </li>
            </template>
            <template v-else>
              <li class="mobile-nav-item">
                <div class="welcome-text w-100">Welcome, {{ auth.user?.name }}</div>
              </li>
              <li class="mobile-nav-item">
                <button v-if="auth.user?.role === 'waiter'" @click="goToWaiter" class="nav-button waiter w-100">Waiter Panel</button>
                <button v-else-if="auth.user?.role === 'kitchen'" @click="goToKitchen" class="nav-button kitchen w-100">Kitchen Panel</button>
                <button v-else @click="goToProfile" class="nav-button profile w-100">Profile</button>
              </li>
              <li class="mobile-nav-item">
                <button @click="handleLogout" class="nav-button logout w-100">Logout</button>
              </li>
            </template>
          </ul>
        </div>
      </transition>

      <!-- Desktop Menu -->
      <div class="navbar-links d-none d-md-flex">
        <ul class="nav-list">
          <li class="nav-item">
            <DarkModeToggle />
          </li>
          <!-- Show these buttons only when user is NOT logged in -->
          <li class="nav-item">
              <LanguageSelector/>
            </li>    
          <template v-if="!auth.token">
            <li class="nav-item">
              <button @click="goToRegister" class="nav-button register">{{ t('navbar.register') }}</button>
            </li>            
            <li class="nav-item">             
              <button @click="goToLogin" class="nav-button login">{{ t('navbar.login') }}</button>
            </li>
          </template>
          <!-- Show profile and logout when user is logged in -->
          <template v-else>
            <li class="nav-item user-menu">
              <div class="welcome-text">Welcome, {{ auth.user?.name }}</div>
              <button v-if="auth.user?.role === 'waiter'" @click="goToWaiter" class="nav-button waiter">Waiter Panel</button>
              <button v-else-if="auth.user?.role === 'kitchen'" @click="goToKitchen" class="nav-button kitchen">Kitchen Panel</button>
              <button v-else @click="goToProfile" class="nav-button profile">Profile</button>
              <button @click="handleLogout" class="nav-button logout">Logout</button>
            </li>
          </template>
        </ul>
      </div>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { useRouter, useRoute } from 'vue-router' 
import { ref, watch, onMounted } from 'vue';
import ButtonComponet from './ButtonComponet.vue';
import DarkModeToggle from './DarkModeToggle.vue';
import { useAuthStore } from '~/stores/auth';
import { useI18n } from '#imports'
const { t } = useI18n()


const auth = useAuthStore();
const router = useRouter();
const route = useRoute();
const isNavbarOpen = ref(false);

const toggleNavbar = () => {
  isNavbarOpen.value = !isNavbarOpen.value;
};

const closeNavbar = () => {
  isNavbarOpen.value = false;
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

const goToWaiter = () => {
  router.push('/waiter');
};

const goToKitchen = () => {
  router.push('/kitchen');
};

const handleLogout = async () => {
  const isProfilePage = route.path === '/profile';
  await auth.logout();
  router.push(isProfilePage ? '/' : '/auth');
};

const goBack = () => {
  router.back();
};

// Close menu on route change
watch(() => route.path, () => {
  closeNavbar();
});

onMounted(() => {
  // Close menu on escape key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeNavbar();
  });
});
</script>
  
<style scoped>
.custom-navbar {
  background: linear-gradient(to right, #ffffff, #f8f9fa);
  padding: 1rem 0;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 1000;
  transition: background-color 0.3s ease;
}

:root.dark .custom-navbar {
  background: linear-gradient(to right, #1a1a1a, #2d2d2d);
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
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
  align-items: center; /* Add this to vertically align items */
}

.nav-button {
  padding: 0.6rem 1.8rem;
  border-radius: 8px;
  font-weight: 600;
  transition: all 0.3s ease;
  cursor: pointer;
  border: none;
  margin: 0 0.5rem;
  font-size: 0.95rem;
  letter-spacing: 0.5px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.nav-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.register {
  background: linear-gradient(45deg, #dd6013, #ffbd00);
  color: white;
  border: none;
}

.register:hover {
  background: linear-gradient(45deg, #c55511, #e5a800);
}

.login {
  background: transparent;
  color: #dd6013;
  border: 2px solid #dd6013;
  position: relative;
  overflow: hidden;
  z-index: 1;
}

.login:hover {
  color: white;
}

.login::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: #dd6013;
  transition: all 0.3s ease;
  z-index: -1;
}

.login:hover::before {
  left: 0;
}

.profile, .waiter, .kitchen {
  background: #dd6013;
  color: white;
  border: none;
}

.profile:hover, .waiter:hover, .kitchen:hover {
  background: #c55511;
}

.waiter {
  background: #2e8b57; /* Sea Green */
}

.waiter:hover {
  background: #21703f;
}

.kitchen {
  background: #4169e1; /* Royal Blue */
}

.kitchen:hover {
  background: #3151b5;
}

.logout {
  background: transparent;
  color: #dc3545;
  border: 2px solid #dc3545;
  position: relative;
  overflow: hidden;
  z-index: 1;
}

.logout:hover {
  color: white;
}

.logout::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: #dc3545;
  transition: all 0.3s ease;
  z-index: -1;
}

.logout:hover::before {
  left: 0;
}

.welcome-text {
  margin-right: 1rem;
  font-weight: 600;
  font-size: 1rem;
  color: #2c3e50;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  background: rgba(221, 96, 19, 0.1);
  display: inline-block;
}

:root.dark .welcome-text {
  color: #e0e0e0;
  background: rgba(221, 96, 19, 0.2);
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
  border: 2px solid #dd6013;
  padding: 0.5rem 1.2rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  border-radius: 8px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.back-button:hover {
  background: #dd6013;
  color: white;
  transform: translateX(-2px);
}

/* Mobile Menu Styles */
.mobile-menu-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 1000;
}

.mobile-menu {
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  width: 80%;
  max-width: 300px;
  background: white;
  z-index: 1001;
  padding: 1rem;
  box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
  overflow-y: auto;
}

:root.dark .mobile-menu {
  background: #1a1a1a;
  box-shadow: -2px 0 10px rgba(0, 0, 0, 0.3);
}

.mobile-menu-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 1rem;
  margin-bottom: 1rem;
  border-bottom: 1px solid #eee;
}

:root.dark .mobile-menu-header {
  border-bottom-color: #333;
}

.mobile-menu-title {
  font-size: 1.2rem;
  font-weight: 600;
}

.close-button {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0.5rem;
  color: #333;
}

:root.dark .close-button {
  color: #fff;
}

.mobile-nav-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.mobile-nav-item {
  margin: 0.5rem 0;
  opacity: 0;
  animation: slideIn 0.3s ease-out forwards;
}

.mobile-nav-item:nth-child(1) { animation-delay: 0.1s; }
.mobile-nav-item:nth-child(2) { animation-delay: 0.2s; }
.mobile-nav-item:nth-child(3) { animation-delay: 0.3s; }

/* Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
  transition: transform 0.3s ease-out;
}

.slide-enter-from,
.slide-leave-to {
  transform: translateX(100%);
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateX(20px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

/* Update existing media queries */
@media (max-width: 768px) {
  .navbar-toggler {
    display: flex;
    z-index: 1002;
  }

  .welcome-text {
    text-align: center;
    margin: 0;
    width: 100%;
  }

  .nav-button {
    margin: 0.5rem 0;
  }
}
</style>