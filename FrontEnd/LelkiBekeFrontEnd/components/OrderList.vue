<template>
  <div class="orders-list" v-if="groupedOrders.length">
    <!-- Compact header with stats and controls -->
    <div class="orders-header">
      <div class="order-stats">
        <span class="active-orders">
          Active Orders: {{ groupedOrders.length }}
        </span>
      </div>
      
      <div class="order-controls">
        <div class="filter-group">
          <button 
            v-for="status in ['all', 'critical', 'warning', 'normal']" 
            :key="status"
            :class="['filter-btn', { active: currentFilter === status }]"
            @click="handleFilterClick(status)"
          >
            {{ status.charAt(0).toUpperCase() + status.slice(1) }}
          </button>
        </div>
        <select v-model="sortOrder" class="sort-select">
          <option value="oldest">Oldest First</option>
          <option value="newest">Newest First</option>
        </select>
      </div>
    </div>

    <!-- Wrap orders in transition-group -->
    <div class="orders-container">
      <!-- Remove TransitionGroup and use a simple div -->
      <div class="orders-list">
        <div v-for="order in filteredAndSortedOrders" 
             :key="order.order_id" 
             class="order-row"
             :class="getOrderAgeClass(order.order_date)">
          <div class="order-header">
            <div class="order-meta">
              <span class="order-id">#{{ order.order_id }}</span>
              <div class="order-status-group">
                <span class="time-passed" :class="getOrderAgeClass(order.order_date)">
                  {{ getOrderAge(order.order_date) }}m
                </span>
                <span v-if="showTableInfo" class="table-number">T{{ order.table_id }}</span>
                <span class="order-time">{{ formatTime(order.order_date) }}</span>
              </div>
            </div>
          </div>

          <div class="items-container">
            <div class="items-grid">
              <div v-for="(item, index) in order.items" 
                   :key="`${order.order_id}-${index}`"
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

  <!-- Confirmation Modal -->
  <div v-if="showConfirmation" class="modal-overlay" @click="showConfirmation = false">
    <div class="modal-content" @click.stop>
      <h3>Confirm Action</h3>
      <p>Mark this order as served?</p>
      <div class="modal-actions">
        <button class="cancel-button" @click="showConfirmation = false">Cancel</button>
        <button class="confirm-button" @click="markAsServed">Confirm</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, watch } from 'vue';
import axios from 'axios';

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

// Initialize reactive refs
const showConfirmation = ref(false);
const selectedOrder = ref(null);
const currentFilter = ref('all');
const sortOrder = ref('oldest');

// Clean up the watcher
watch(
  () => props.orders,
  () => {
    nextTick(() => {
      sortOrder.value = sortOrder.value === 'oldest' ? 'newest' : 'oldest';
      nextTick(() => {
        sortOrder.value = sortOrder.value === 'oldest' ? 'newest' : 'oldest';
      });
    });
  },
  { deep: true }
);

// Simplify groupedOrders computed
const groupedOrders = computed(() => {
  return props.orders.reduce((acc, order) => {
    const existingOrder = acc.find(o => o.order_id === order.order_id);
    if (existingOrder) {
      existingOrder.items.push({
        menu_item_name: order.menu_item_name,
        quantity: order.quantity,
        notes: order.notes,
      });
    } else {
      acc.push({
        order_id: order.order_id,
        table_id: order.table_id,
        order_date: order.order_date,
        status: order.status,
        total_price: order.total_price,
        items: [{
          menu_item_name: order.menu_item_name,
          quantity: order.quantity,
          notes: order.notes,
        }],
      });
    }
    return acc;
  }, []);
});

// Sort orders by date
const sortedOrders = computed(() => {
  return [...groupedOrders.value].sort((a, b) => {
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

const getOrderAge = (dateString) => {
  const orderTime = new Date(dateString);
  const now = new Date();
  return Math.floor((now - orderTime) / 60000); // Returns minutes
};

const calculateAverageWaitTime = () => {
  if (!groupedOrders.value.length) return '0 min';
  const total = groupedOrders.value.reduce((sum, order) => {
    return sum + getOrderAge(order.order_date);
  }, 0);
  return `${Math.round(total / groupedOrders.value.length)} min`;
};

const getOrderAgeClass = (dateString) => {
  const age = getOrderAge(dateString);
  if (age > 20) return 'critical';
  if (age > 15) return 'warning';
  if (age > 10) return 'attention';
  return 'normal';
};

const averageWaitTime = computed(() => {
  return calculateAverageWaitTime();
});

const urgentOrders = computed(() => {
  return groupedOrders.value.filter(order => 
    getOrderAge(order.order_date) > 15
  ).length;
});

const filteredAndSortedOrders = computed(() => {
  let orders = [...sortedOrders.value];
  
  // Apply filter
  if (currentFilter.value !== 'all') {
    orders = orders.filter(order => 
      getOrderAgeClass(order.order_date) === currentFilter.value
    );
  }
  
  // Apply sort
  if (sortOrder.value === 'newest') {
    orders.reverse();
  }
  
  return orders;
});

const getUrgencyClass = (minutes) => {
  if (minutes > 20) return 'critical';
  if (minutes > 15) return 'warning';
  return 'normal';
};

// Order actions
const confirmMarkAsServed = (order) => {
  selectedOrder.value = order;
  showConfirmation.value = true;
};

const markAsServed = async () => {
  if (selectedOrder.value) {
    try {
      await axios.post('http://localhost:8000/api/setOrderStatus', {
        order_id: selectedOrder.value.order_id,
        status: 'done'
      });
      showConfirmation.value = false;
      emit('order-updated');
    } catch (error) {
      console.error('Error marking order as served:', error);
    }
  }
};

const handleFilterClick = (status) => {
  currentFilter.value = status;
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
}

.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 16px;
  width: 90%;
  max-width: 400px;
  text-align: center;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  margin-top: 2rem;
}

.cancel-button, .confirm-button {
  padding: 1rem 2rem;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
}

.cancel-button {
  background: #e9ecef;
  color: #2c3e50;
}

.confirm-button {
  background: linear-gradient(45deg, #2ecc71, #27ae60);
  color: white;
}

@media (max-width: 768px) {
  .order-card {
    padding: 1rem;
  }
  
  .serve-button {
    padding: 1.2rem;
    font-size: 1.3rem;
  }
}

.order-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.stat-item {
  background: white;
  padding: 1rem;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.stat-label {
  color: #666;
  font-size: 0.9rem;
  display: block;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 600;
  color: #2c3e50;
}

.order-time {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.time-ago {
  color: #666;
  font-size: 0.9rem;
}

.attention-needed {
  border-left: 4px solid #e74c3c;
  animation: pulse 2s infinite;
}

.priority-indicator {
  color: #e74c3c;
  font-weight: 600;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.priority-indicator::before {
  content: '⚠️';
}

@keyframes pulse {
  0% { box-shadow: 0 0 0 0 rgba(231, 76, 60, 0.4); }
  70% { box-shadow: 0 0 0 10px rgba(231, 76, 60, 0); }
  100% { box-shadow: 0 0 0 0 rgba(231, 76, 60, 0); }
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

.sort-select {
  margin-left: auto;
  padding: 0.5rem;
  border-radius: 8px;
  border: 1px solid #dee2e6;
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

.serve-button {

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

.sort-select {
  padding: 0.4rem;
  border-radius: 8px;
  border: 1px solid #dee2e6;
  font-size: 0.9rem;
  background: white;
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
  padding: 0.3rem 0.6rem;
  border-radius: 6px;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.9rem;
}

.status-badge.cooking {
  background: #f39c12;
  color: white;
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
</style>