<template>
  <div class="notifications-wrapper">
    <TransitionGroup name="notification" tag="div" class="notification-list">
      <div v-for="notification in activeNotifications" 
           :key="notification.id" 
           class="notification-item">
        <div class="notification-content">
          <strong>Table {{ notification.tableId }}</strong>
          <span class="time-ago">{{ getTimeAgo(notification.timestamp) }}</span>
        </div>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const notifications = ref([]);
const maxVisible = 5;

const activeNotifications = computed(() => {
  return notifications.value.slice(-maxVisible);
});

const addNotification = (tableId, duration = 300000) => { // 5 minutes
  const id = Date.now();
  notifications.value.push({
    id,
    tableId,
    timestamp: new Date()
  });
  
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

const getTimeAgo = (timestamp) => {
  const seconds = Math.floor((new Date() - timestamp) / 1000);
  
  if (seconds < 60) return 'just now';
  if (seconds < 120) return '1 minute ago';
  if (seconds < 3600) return Math.floor(seconds / 60) + ' minutes ago';
  if (seconds < 7200) return '1 hour ago';
  return Math.floor(seconds / 3600) + ' hours ago';
};

defineExpose({ addNotification });
</script>

<style scoped>
.notifications-wrapper {
  position: fixed;
  top: 20px;
  right: 20px;
  max-width: 300px;
  z-index: 1000;
}

.notification-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.notification-item {
  background: rgba(255, 255, 255, 0.95);
  padding: 12px 16px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  border-left: 4px solid #2ecc71;
}

.notification-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
}

.time-ago {
  font-size: 0.8rem;
  color: #666;
}

.notification-enter-active,
.notification-leave-active {
  transition: all 0.3s ease;
}

.notification-enter-from,
.notification-leave-to {
  opacity: 0;
  transform: translateX(30px);
}
</style>
