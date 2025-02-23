<template>
  <div class="notification-stack">
    <div class="notification-overflow" :class="{ 'has-more': notifications.length > maxVisible }">
      <TransitionGroup name="notification">
        <div v-for="notification in visibleNotifications" 
             :key="notification.id" 
             class="notification">
          <span class="message">{{ notification.message }}</span>
          <button class="close-button" @click="removeNotification(notification.id)">×</button>
        </div>
      </TransitionGroup>
      <div v-if="notifications.length > maxVisible" class="overflow-indicator">
        +{{ notifications.length - maxVisible }} more notifications
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const notifications = ref([]);
const maxVisible = 3; // Maximum number of visible notifications

const visibleNotifications = computed(() => {
  return notifications.value.slice(-maxVisible);
});

const addNotification = (message, duration = 60000) => {
  console.log('Adding notification:', message); // Debug log
  const id = Date.now();
  notifications.value.push({ id, message });
  
  setTimeout(() => {
    removeNotification(id);
  }, duration);
};

const removeNotification = (id) => {
  const index = notifications.value.findIndex(n => n.id === id);
  if (index > -1) {
    notifications.value.splice(index, 1);
  }
};

defineExpose({ addNotification });
</script>

<style scoped>
.notification-stack {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 1000;
  max-height: calc(100vh - 40px);
  pointer-events: none; /* Allow clicking through the container */
}

.notification-overflow {
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-height: 100%;
  overflow: hidden;
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
  max-width: 400px;
  pointer-events: auto; /* Re-enable pointer events for notifications */
}

.message {
  flex-grow: 1;
  word-break: break-word;
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

.overflow-indicator {
  background-color: rgba(0, 0, 0, 0.7);
  color: white;
  padding: 8px 16px;
  border-radius: 6px;
  text-align: center;
  font-size: 14px;
  margin-top: 8px;
  pointer-events: auto;
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
