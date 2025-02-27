<template>
  <div class="orders-list" v-if="groupedOrders.length">
    <!-- Compact header with stats and controls -->
    <div class="orders-header">
      <div class="order-stats">
        <div class="stats-grid">
          <div class="stat-item">
            <span class="stat-value">{{ orderCounts.pending }}</span>
            <span class="stat-label">{{ t('orderList.pending') }}</span>
          </div>
          <div class="stat-item">
            <span class="stat-value">{{ orderCounts.cooking }}</span>
            <span class="stat-label">{{ t('orderList.cooking') }}</span>
          </div>
          <div class="stat-item">
            <span class="stat-value">{{ orderCounts.cooked }}</span>
            <span class="stat-label">{{ t('orderList.ready') }}</span>
          </div>
          <div class="stat-item total">
            <span class="stat-value">{{ groupedOrders.length }}</span>
            <span class="stat-label">{{ t('orderList.total') }}</span>
          </div>
        </div>
      </div>
      
      <div class="order-controls">
        <select v-model="sortOrder" class="sort-select" @change="handleSort">
          <option value="newest">{{ t('orderList.nFirst') }}</option>
          <option value="oldest">{{ t('orderList.oFirst') }}</option>
        </select>
      </div>
    </div>

    <!-- Wrap orders in transition-group -->
    <div class="orders-container">
      <!-- Remove TransitionGroup and use a simple div -->
      <div class="orders-list">
        <div v-for="order in filteredAndSortedOrders" 
             :key="`${order.order_id}-${orderStore.getOrderPosition(order.order_id)}`" 
             class="order-row"
             :class="getOrderAgeClass(getOrderAge(order.order_id))">
          <div class="order-header">
            <div class="order-meta">
              <span class="order-id">#{{ order.order_id }}</span>
              <div class="order-status-group">
                <span class="status-badge" :class="order.status">
                  {{ order.status.toUpperCase() }}
                </span>
                <span class="time-passed" :class="getOrderAgeClass(getOrderAge(order.order_id))">
                  {{ getOrderAge(order.order_id) }}m
                </span>
                <span v-if="showTableInfo" class="table-number">T{{ order.table_id }}</span>
                <span class="order-time">{{ formatTime(order.order_date) }}</span>
              </div>
            </div>
          </div>

          <div class="items-container">
            <div class="items-grid">
              <div v-for="(item, index) in sortedItems(order)" 
                   :key="`${order.order_id}-item-${index}`"
                   class="item-row">
                <span class="item-quantity">{{ item.quantity }}×</span>
                <span class="item-name">{{ item.menu_item_name }}</span>
                <span v-if="item.notes && item.notes !== 'null'" 
                      class="item-notes">
                  "{{ item.notes }}"
                </span>
              </div>
            </div>
          </div>

          <div class="action-container">
            <button v-if="order.status !== 'done'"
                    class="serve-button"
                    :class="getOrderAgeClass(order.order_date)"
                    @click.stop="confirmMarkAsServed(order)">
              Mark as Served
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div v-else class="empty-state">No active orders</div>

  <ConfirmationModal
    :show="showConfirmation"
    title="Confirm Order Status"
    message="Are you sure you want to mark this order as served?"
    :orderDetails="selectedOrder"
    @confirm="markAsServed"
    @cancel="closeConfirmation"
    @close="closeConfirmation"
  />
</template>

<script setup>
import { ref, computed, nextTick, watch, onBeforeUnmount } from 'vue';
import axios from 'axios';
import ConfirmationModal from './ConfirmationModal.vue';
import { useOrderStore } from '@/stores/orderStore';
import { useI18n } from '#imports'
const { t } = useI18n()

import { useGlobalTimer } from '@/composables/useGlobalTimer';

const orderStore = useOrderStore();
const timer = useGlobalTimer();

const props = defineProps({
  orders: {
    type: Array,
    required: true
  },
  showTableInfo: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['order-updated']);

// Remove scroll position management
const showConfirmation = ref(false);
const selectedOrder = ref(null);
const sortOrder = ref('newest');

// Remove the orders watcher that was managing scroll position

// Keep only modal-related scroll lock
watch(showConfirmation, (isVisible) => {
  if (isVisible) {
    document.body.style.overflow = 'hidden';
  } else {
    document.body.style.overflow = '';
  }
});

// Clean up on component unmount
onBeforeUnmount(() => {
  document.body.style.overflow = '';
  props.orders.forEach(order => {
    timer.stopTracking(order.order_id);
  });
});

// Update formatOrderItems if items are in string format
// Update the formatItems function to handle both array and object formats
const formatItems = (order) => {
  if (!order.items) return [];
  
  // If items is already parsed as an array
  if (Array.isArray(order.items)) {
    return order.items;
  }
  
  // If items is a JSON string
  if (typeof order.items === 'string') {
    try {
      return JSON.parse(order.items);
    } catch (e) {
      console.error('Error parsing items:', e);
    }
  }
  
  return [];
};

// Update the groupedOrders computed property for better performance
const groupedOrders = computed(() => {
  if (!props.orders) return [];
  
  return props.orders.map(order => {
    const items = formatItems(order);
    
    // Start tracking time for this order if not already tracking
    timer.startTracking(order.order_id, order.order_date);
    
    return {
      ...order,
      items: items.map((item, index) => ({
        ...item,
        sortIndex: index // Simplify item sorting
      }))
    };
  });
});

// Replace the filteredAndSortedOrders computed property
const filteredAndSortedOrders = computed(() => {
  let orders = groupedOrders.value;
  
  return orders.sort((a, b) => {
    // Define status priority (higher number = higher priority)
    const statusPriority = {
      'cooked': 3,
      'cooking': 2,
      'pending': 1
    };

    // Compare by status priority first
    const priorityDiff = statusPriority[b.status] - statusPriority[a.status];
    if (priorityDiff !== 0) return priorityDiff;

    // If same status, sort by time (oldest first)
    return new Date(a.order_date) - new Date(b.order_date);
  });
});

// Time formatting functions
const formatTime = (dateString) => {
  return new Date(dateString).toLocaleTimeString('en-GB', { 
    hour: '2-digit',
    minute: '2-digit',
    hour12: false
  });
};

// Replace getOrderAge with this version
const getOrderAge = (orderId) => {
  const order = props.orders.find(o => o.order_id === orderId);
  if (!order) return 0;
  
  // Start tracking if not already tracking
  timer.startTracking(order.order_id, order.order_date);
  return timer.getElapsedTime(order.order_id);
};

// Add getOrderAgeClass function that was missing
const getOrderAgeClass = (minutes) => {
  if (minutes > 20) return 'critical';
  if (minutes > 15) return 'warning';
  if (minutes > 10) return 'attention';
  return 'normal';
};

const calculateAverageWaitTime = () => {
  if (!groupedOrders.value.length) return '0 min';
  const total = groupedOrders.value.reduce((sum, order) => {
    return sum + getOrderAge(order.order_date);
  }, 0);
  return `${Math.round(total / groupedOrders.value.length)} min`;
};

const averageWaitTime = computed(() => {
  return calculateAverageWaitTime();
});

const urgentOrders = computed(() => {
  return groupedOrders.value.filter(order => 
    getOrderAge(order.order_date) > 15
  ).length;
});

const getUrgencyClass = (minutes) => {
  if (minutes > 20) return 'critical';
  if (minutes > 15) return 'warning';
  return 'normal';
};

// Order actions
const confirmMarkAsServed = (order) => {
  console.log('Order being marked as served:', order); // Add debugging
  selectedOrder.value = {
    order_id: order.order_id,
    table_id: order.table_id,  // Make sure table_id exists in the order object
    status: order.status
  };
  console.log('Selected order details:', {
    order_id: order.order_id,
    table_id: order.table_id,
    status: order.status
  });
  showConfirmation.value = true;
};

// Update markAsServed function with better error handling
const markAsServed = async () => {
  if (!selectedOrder.value || !selectedOrder.value.table_id) {
    console.error('Invalid order or missing table_id:', selectedOrder.value);
    // Add user feedback
    alert('Error: Unable to mark order as served - missing table information');
    return;
  }

  try {
    console.log('Attempting to mark order as served:', selectedOrder.value);
    const originalPosition = orderStore.getOrderPosition(selectedOrder.value.order_id);
    await axios.post('https://api.innerpeace.jedlik.cloud/api/kitchen/update-status', {
      order_id: selectedOrder.value.order_id,
      status: 'served',
      table_id: selectedOrder.value.table_id,
      originalPosition
    });

    // Update local order status without refreshing the entire list
    orderStore.updateOrderStatus(selectedOrder.value.order_id, 'served');
    
    closeConfirmation();
    emit('order-updated');
  } catch (error) {
    console.error('Error marking order as served:', error);
  }
};

const handleSort = () => {
  // Empty function - no scroll handling needed
};

const orderCounts = computed(() => {
  return groupedOrders.value.reduce((acc, order) => {
    acc[order.status] = (acc[order.status] || 0) + 1;
    return acc;
  }, { pending: 0, cooking: 0, cooked: 0 });
});

const closeConfirmation = () => {
  showConfirmation.value = false;
  selectedOrder.value = null;
};

// Update sortedItems function to maintain consistent order
const sortedItems = (order) => {
  if (!order.items) return [];
  return order.items; // Items are already sorted by their index
};
</script>

<style scoped>
.orders-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  padding: 0.5rem;
}

.order-row {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  width: 100%;
  min-height: 120px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.items-list {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  padding: 1rem 0;
}

.item-row {
  flex: 1;
  min-width: 250px;
  padding: 0.8rem;
  background: #f8f9fa;
  border-radius: 8px;
  border: none;
}

.action-container {
  display: flex;
  justify-content: flex-end;
  padding-top: 1rem;
  border-top: 1px solid #eee;
  margin-top: auto;
}

/* Mobile optimizations */
@media (max-width: 768px) {
  .orders-container {
    padding: 0.5rem;
  }

  .order-row {
    padding: 1rem;
  }

  .item-row {
    min-width: 100%;
  }

  .serve-button {
    width: 100%;
  }
}

.order-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  height: 100%;
  min-height: 200px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.items-list {
  flex: 1;
  overflow-y: auto;
  margin: 1rem 0;
}

.item-row {
  padding: 0.8rem;
  border-bottom: 1px solid #eee;
}

.item-row:last-child {
  border-bottom: none;
}

.item-details {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  align-items: center;
}

.item-quantity {
  color: #3498db;
  font-weight: 600;
  font-size: 1.2rem;
  min-width: 2.5rem;
}

.item-name {
  flex: 1;
  font-size: 1.1rem;
}

.item-notes {
  width: 100%;
  margin-top: 0.3rem;
  color: #666;
  font-style: italic;
  font-size: 0.9rem;
  padding-left: 3rem;
}

.serve-button {
  background: #2ecc71;
  color: white;
  border: none;
  width: 100%;
  padding: 1.5rem;
  border-radius: 12px;
  font-weight: 600;
  font-size: 1.3rem;
  cursor: pointer;
  transition: all 0.2s ease;
  text-transform: uppercase;
  letter-spacing: 1px;
  box-shadow: 0 4px 6px rgba(46, 204, 113, 0.2);
}

.serve-button:active {
  transform: scale(0.98);
  background: #27ae60;
}

/* Adjust touch targets for mobile */
@media (max-width: 768px) {
  .serve-button {
    padding: 2rem;
    font-size: 1.4rem;
  }

  .item-quantity {
    font-size: 1.3rem;
    min-width: 3rem;
  }

  .item-name {
    font-size: 1.2rem;
  }
}

.header-info {
  grid-column: 1 / -1;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.order-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  padding: 0.5rem;
  background: #f8f9fa;
  border-radius: 8px;
}

.meta-group {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.order-id {
  font-size: 1.5rem;
  font-weight: 700;
  color: #2c3e50;
  margin-left: 1rem;  /* Added left margin */
}

.table-number {
  background: #3498db;
  color: white;
  padding: 0.4rem 1rem;
  border-radius: 6px;
  font-size: 1.1rem;
  font-weight: 600;
  min-width: 50px;
  text-align: center;
}

.time-passed {
  font-weight: 600;
  padding: 0.4rem 1rem;
  border-radius: 6px;
  min-width: 70px;
  text-align: center;
}

.order-time {
  background: #eee;
  padding: 0.4rem 1rem;
  border-radius: 6px;
  font-weight: 500;
  color: #444;
  min-width: 75px;
  text-align: center;
}

.time-passed.normal { color: #2ecc71; }
.time-passed.attention { 
  color: #f39c12;
  background: #ffeaa7;
}
.time-passed.warning { 
  color: #e67e22;
  background: #fad7a0;
}
.time-passed.critical { 
  color: white;
  background: #e74c3c;
}

.order-card.warning { border-left: 4px solid #f39c12; }
.order-card.critical { border-left: 4px solid #e74c3c; }

.empty-state {
  text-align: center;
  padding: 3rem;
  background: #f8f9fa;
  border-radius: 16px;
  color: #6c757d;
}

.empty-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
  /* Prevent scrolling of background */
  overflow: hidden;
  -webkit-overflow-scrolling: touch;
}

.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 16px;
  width: 90%;
  max-width: 400px;
  text-align: center;
  /* Center modal and prevent overflow */
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  /* Add max height and scrolling for very tall screens */
  max-height: 90vh;
  overflow-y: auto;
  /* Improve appearance */
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  margin-top: 2rem;
  /* Ensure buttons stay at bottom of modal */
  position: sticky;
  bottom: 0;
  background: white;
  padding-top: 1rem;
  border-top: 1px solid #eee;
}

/* Add responsive padding for mobile */
@media (max-width: 768px) {
  .modal-overlay {
    padding: 0.5rem;
  }
  
  .modal-content {
    padding: 1.5rem;
  }
}

.quick-stats {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
  padding: 1rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stat {
  flex: 1;
  text-align: center;
  padding: 0.5rem;
  border-radius: 8px;
}

.stat-value {
  display: block;
  font-size: 1.5rem;
  font-weight: bold;
  margin-bottom: 0.2rem;
}

.stat-label {
  font-size: 0.9rem;
  color: #666;
}

.order-controls {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

.filter-btn {
  padding: 0.7rem 1.2rem;
  border: none;
  border-radius: 20px;
  background: #f8f9fa;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  min-width: 100px;
}

.filter-btn.active {
  background: #3498db;
  color: white;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(52, 152, 219, 0.2);
}

.filter-btn:hover {
  background: #e9ecef;
}

.filter-btn.active:hover {
  background: #2980b9;
}

.order-row {
  cursor: pointer;
  transition: all 0.3s ease;
}

.order-row:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.order-content {
  display: block;
  padding: 1rem 0;
}

.items-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin: 1rem 0;
}



.serve-button.critical {
  background: #e74c3c;
  animation: pulse 2s infinite;
}

.serve-button.warning {
  background: #f39c12;
}

.orders-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 1rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  margin-bottom: 1rem;
}

.order-stats {
  display: flex;
  align-items: center;
}

.active-orders {
  font-size: 1.2rem;
  font-weight: 600;
  color: #2c3e50;
}

.order-controls {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.filter-group {
  display: flex;
  gap: 0.3rem;
}

.filter-btn {
  padding: 0.4rem 0.8rem;
  border: none;
  border-radius: 20px;
  background: #f8f9fa;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s;
}

.filter-btn.active {
  background: #3498db;
  color: white;
}

@media (max-width: 768px) {
  .orders-header {
    flex-direction: column;
    gap: 0.8rem;
    padding: 0.8rem;
  }

  .order-controls {
    width: 100%;
    flex-wrap: wrap;
    justify-content: space-between;
  }

  .filter-group {
    flex-wrap: wrap;
    justify-content: center;
  }
}

.order-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  padding: 0.5rem 0;
}

.order-status-group {
  display: flex;
  align-items: center;
  gap: 1.2rem;
  background: #f8f9fa;
  padding: 0.5rem 1rem;
  border-radius: 8px;
}

.order-id {
  font-size: 1.5rem;
  font-weight: 700;
  color: #2c3e50;
  margin-left: 1rem;  /* Added left margin */
}

.table-number {
  background: #3498db;
  color: white;
  padding: 0.4rem 1rem;
  border-radius: 6px;
  font-weight: 600;
}

.time-passed {
  font-weight: 600;
  font-size: 1.1rem;
  padding: 0.4rem 1rem;
  border-radius: 6px;
  min-width: 70px;
  text-align: center;
}

.order-time {
  font-weight: 500;
  color: #666;
  padding: 0.4rem 1rem;
  min-width: 75px;
  text-align: center;
}

.status-badge {
  padding: 0.4rem 0.8rem;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-badge.pending {
  background: #f1c40f;
  color: #000;
}

.status-badge.cooking {
  background: #e67e22;
  color: white;
}

.status-badge.cooked {
  background: #2ecc71;
  color: white;
}

@media (max-width: 768px) {
  .order-status-group {
    flex-wrap: wrap;
    gap: 0.5rem;
  }
  
  .status-badge {
    padding: 0.3rem 0.6rem;
    font-size: 0.8rem;
  }
}

@media (max-width: 768px) {
  .items-grid {
    grid-template-columns: 1fr;
  }

  .order-status-group {
    flex-wrap: wrap;
    gap: 0.5rem;
}
}

.items-container {
  padding: 1rem 0;
  overflow: hidden;
}

.items-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 0.8rem;
  margin: 0;
  padding: 0;
}

@media (max-width: 576px) {
  .items-grid {
    grid-template-columns: 1fr;
  }
  
  .item-row {
    padding: 0.8rem;
  }

  .item-name {
    font-size: 0.95rem;
  }
}

.item-row {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 1rem;
  background: #f8f9fa;
  padding: 1rem;
  border-radius: 8px;
  min-width: 0; /* Prevents overflow */
}

.item-quantity {
  color: #3498db;
  font-weight: 600;
  font-size: 1.2rem;
  padding: 0 0.5rem;
}

.item-name {
  flex: 1;
  font-size: 1rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.item-notes {
  width: 100%;
  margin-top: 0.5rem;
  color: #666;
  font-style: italic;
  font-size: 0.9rem;
  text-align: center;  /* Center align the notes */
  padding: 0.3rem 0;   /* Remove left padding, add vertical padding */
  border-top: 1px solid #eee;  /* Optional: adds a subtle separator */
}

.order-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  padding: 0.5rem 0;
  border-bottom: 1px solid #eee;
  margin-bottom: 0.5rem;
}

/* Remove all transition related styles */
.fade-list-enter-active,
.fade-list-leave-active,
.fade-list-enter-from,
.fade-list-leave-to,
.fade-list-move {
  display: none;
}

.orders-container {
  position: relative;
  min-height: 100px;
}

.orders-list {
  position: relative;
  width: 100%;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  padding: 0.5rem;
}

.stat-item {
  text-align: center;
  padding: 0.5rem;
  border-radius: 8px;
  background: #f8f9fa;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  display: block;
  color: #2c3e50;
}

.stat-label {
  font-size: 0.9rem;
  color: #666;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-item.total {
  background: #3498db;
}

.stat-item.total .stat-value,
.stat-item.total .stat-label {
  color: white;
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .stat-item.total {
    grid-column: span 2;
  }
}

/* Add or modify these styles */
.orders-container {
  contain: paint;
  min-height: min-content;
}

.orders-list {
  contain: paint;
  position: relative;
  width: 100%;
  overflow: visible;
}

/* Disable any smooth-scroll behaviors */
* {
  scroll-behavior: unset !important;
}

.sort-select {
  padding: 0.8rem 1.2rem;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  font-size: 1rem;
  color: #2c3e50;
  background-color: white;
  cursor: pointer;
  min-width: 150px;
}

.sort-select:hover {
  border-color: #3498db;
}

.sort-select:focus {
  outline: none;
  border-color: #3498db;
  box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
}

.sort-select:hover {
  border-color: #3498db;
}

.sort-select:focus {
  outline: none;
  border-color: #3498db;
  box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
}
</style>