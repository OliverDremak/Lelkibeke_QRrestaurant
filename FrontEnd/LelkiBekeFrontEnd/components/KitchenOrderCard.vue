<template>
  <div class="kitchen-order" :class="[status, orderUrgencyClass]">
    <header class="order-header">
      <div class="order-info">
        <span class="order-number">#{{ order.order_id }}</span>
        <span class="table-number">{{ t('kitchenOrderCard.table') }} {{ order.table_id }}</span>
      </div>
      
      <div class="time-info">
        <span class="elapsed-time" :class="orderUrgencyClass">
          {{ elapsedTime }}m
        </span>
        <div class="time-details">
          <span class="time-value">{{ t('kitchenOrderCard.created') }}: {{ formatTime(order.order_date) }}</span>
        </div>
      </div>
    </header>

    <div class="items-list">
      <div v-for="(item, index) in order.items" 
           :key="`${order.order_id}-${index}`" 
           class="item">
        <div class="item-quantity">{{ item.quantity }}×</div>
        <div class="item-details">
          <div class="item-name">{{ item.menu_item_name }}</div>
          <div v-if="item.notes && item.notes !== 'null'" class="item-notes">
            "{{ item.notes }}"
          </div>
        </div>
      </div>
    </div>

    <footer class="order-actions">
      <button 
        class="action-button"
        :class="actionButtonClass"
        @click="handleStatusChange(order.order_id, nextStatus)"
      >
        {{ actionButtonText }}
      </button>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { useOrderStore } from '@/stores/orderStore';

import { useI18n } from '#imports'
const { t } = useI18n()

const orderStore = useOrderStore();

const props = defineProps({
  order: {
    type: Object,
    required: true,
    default: () => ({
      items: []
    })
  },
  status: {
    type: String,
    required: true
  }
});

const emit = defineEmits(['status-change']);

const handleStatusChange = async (orderId, newStatus) => {
  emit('status-change', {
    orderId,
    newStatus,
    originalDate: props.order.order_date // Pass original date to maintain order
  });
};

const elapsedTime = computed(() => {
  return orderStore.getElapsedTime(props.order.order_id);
});

const orderUrgencyClass = computed(() => {
  if (elapsedTime.value > 20) return 'critical';
  if (elapsedTime.value > 15) return 'warning';
  if (elapsedTime.value > 10) return 'attention';
  return 'normal';
});

// Format time helper
const formatTime = (dateString) => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleTimeString('en-GB', {
    hour: '2-digit',
    minute: '2-digit'
  });
};

const nextStatus = computed(() => {
  return props.status === 'pending' ? 'cooking' : 'cooked';
});

const actionButtonText = computed(() => {
  return props.status === 'pending' ? 'Start Cooking' : 'Mark Ready';
});

const actionButtonClass = computed(() => {
  return props.status === 'pending' ? 'start-cooking' : 'mark-ready';
});
</script>

<style scoped>
.kitchen-order {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  display: flex;
  flex-direction: column;
  gap: 1rem;
  border-left: 6px solid #e9ecef;
  transition: all 0.3s ease;
}

.kitchen-order.pending { border-left-color: #f1c40f; }
.kitchen-order.cooking { border-left-color: #e67e22; }
.kitchen-order.critical { border-left-color: #e74c3c; }

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  padding-bottom: 1rem;
  border-bottom: 2px solid #f8f9fa;
}

.order-info {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.order-number {
  font-size: 2rem;
  font-weight: 700;
  color: #2c3e50;
}

.table-number {
  font-size: 1.2rem;
  font-weight: 600;
  color: #7f8c8d;
}

.time-info {
  text-align: right;
}

.elapsed-time {
  font-size: 1.5rem;
  font-weight: 700;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  margin-bottom: 0.5rem;
  display: inline-block;
}

.elapsed-time.normal { color: #2ecc71; }
.elapsed-time.attention { 
  background: #fff3cd;
  color: #856404;
}
.elapsed-time.warning { 
  background: #ffe5d0;
  color: #c44d00;
}
.elapsed-time.critical { 
  background: #ffe0e3;
  color: #dc3545;
  animation: pulse 2s infinite;
}

.actual-time {
  font-size: 1rem;
  color: #95a5a6;
}

.items-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.item {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 12px;
  align-items: center;
}

.item-quantity {
  font-size: 1.5rem;
  font-weight: 700;
  color: #3498db;
  padding: 0.5rem 1rem;
  background: white;
  border-radius: 8px;
  min-width: 3rem;
  text-align: center;
}

.item-details {
  flex: 1;
}

.item-name {
  font-size: 1.2rem;
  font-weight: 600;
  color: #2c3e50;
}

.item-notes {
  margin-top: 0.5rem;
  font-size: 1rem;
  color: #e67e22;
  font-style: italic;
}

.action-button {
  width: 100%;
  padding: 1.2rem;
  border: none;
  border-radius: 12px;
  font-size: 1.2rem;
  font-weight: 600;
  text-transform: uppercase;
  cursor: pointer;
  transition: all 0.3s ease;
}

.action-button.start-cooking {
  background: linear-gradient(135deg, #f39c12, #e67e22);
  color: white;
}

.action-button.mark-ready {
  background: linear-gradient(135deg, #2ecc71, #27ae60);
  color: white;
}

.action-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.action-button:active {
  transform: translateY(0);
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.02); }
  100% { transform: scale(1); }
}

@media (max-width: 768px) {
  .kitchen-order {
    padding: 1rem;
  }

  .order-number {
    font-size: 1.5rem;
  }

  .elapsed-time {
    font-size: 1.2rem;
  }

  .action-button {
    padding: 1.5rem;
    font-size: 1.3rem;
  }
}

.total-time {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 1rem;
}

.time-details {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 0.8rem;
}

.time-line {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.5rem;
  padding: 0.3rem 0;
}

.time-label {
  font-size: 0.9rem;
  color: #666;
}

.time-value {
  font-weight: 600;
}
</style>
