<template>
  <transition name="notification">
    <div v-if="isVisible" class="notification-container">
      <div class="notification">
        <span class="message">{{ message }}</span>
        <button class="close-button" @click="close">×</button>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
  message: {
    type: String,
    required: true
  },
  duration: {
    type: Number,
    default: 60000 // 1 minute in milliseconds
  }
});

const isVisible = ref(true);
let timer = null;

const close = () => {
  isVisible.value = false;
};

onMounted(() => {
  timer = setTimeout(close, props.duration);
});

onUnmounted(() => {
  if (timer) clearTimeout(timer);
});
</script>

<style scoped>
.notification-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 1000;
}

.notification {
  background-color: #28a745;
  color: white;
  padding: 12px 24px;
  border-radius: 6px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 300px;
}

.message {
  flex-grow: 1;
}

.close-button {
  background: none;
  border: none;
  color: white;
  font-size: 24px;
  cursor: pointer;
  padding: 0 4px;
  line-height: 1;
}

.close-button:hover {
  opacity: 0.8;
}

.notification-enter-active,
.notification-leave-active {
  transition: all 0.3s ease;
}

.notification-enter-from {
  transform: translateX(100%);
  opacity: 0;
}

.notification-leave-to {
  transform: translateX(100%);
  opacity: 0;
}
</style>
