<template>
  <div class="kitchen-dashboard">
    <header class="dashboard-header">
      <div class="header-content">
        <h1>Kitchen Dashboard</h1>
        <div class="header-right">
          <div class="order-stats">
            <div class="stat-item" :class="{ attention: pendingOrders.length > 5 }">
              <span class="stat-value">{{ pendingOrders.length }}</span>
              <span class="stat-label">Pending</span>
            </div>
            <div class="stat-item" :class="{ attention: cookingOrders.length > 8 }">
              <span class="stat-value">{{ cookingOrders.length }}</span>
              <span class="stat-label">Cooking</span>
            </div>
            <div class="stat-item">
              <span class="stat-value">{{ orders.length }}</span>
              <span class="stat-label">Total Active</span>
            </div>
          </div>
          <button class="logout-button" @click="logout">Logout</button>
        </div>
      </div>
    </header>

    <div class="orders-grid">
      <section class="orders-column pending">
        <h2>New Orders</h2>
        <div class="orders-list">
          <TransitionGroup name="order">
            <KitchenOrderCard
              v-for="order in pendingOrders"
              :key="order.order_id"
              :order="order"
              :initial-elapsed-time="getInitialElapsedTime(order)"
              status="pending"
              @status-change="updateStatus"
            />
          </TransitionGroup>
        </div>
      </section>

      <section class="orders-column cooking">
        <h2>Currently Cooking</h2>
        <div class="orders-list">
          <TransitionGroup name="order">
            <KitchenOrderCard
              v-for="order in cookingOrders"
              :key="order.order_id"
              :order="order"
              :initial-elapsed-time="getInitialElapsedTime(order)"
              :initial-cooking-time="getCookingElapsedTime(order)"
              status="cooking"
              @status-change="updateStatus"
            />
          </TransitionGroup>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { useNuxtApp, useRouter } from '#app';
import { useAuthStore } from '@/stores/auth';
import KitchenOrderCard from '../components/KitchenOrderCard.vue';  // Update this path
import axios from 'axios';
import { useTimeStore } from '@/stores/timeStore';
import { useOrderStore } from '@/stores/orderStore';

const router = useRouter();
const timeStore = useTimeStore();
const orderStore = useOrderStore();
const authStore = useAuthStore();
const { $ws } = useNuxtApp();
const orders = ref([]);
const currentFilter = ref('all');

const groupedOrders = computed(() => {
  const filtered = currentFilter.value === 'all' 
    ? orders.value 
    : orders.value.filter(order => order.status === currentFilter.value);

  return filtered.reduce((acc, order) => {
    if (!acc[order.table_id]) acc[order.table_id] = [];
    acc[order.table_id].push(order);
    return acc;
  }, {});
});

const pendingOrders = computed(() => 
  orders.value
    .filter(order => order.status === 'pending')
    .sort((a, b) => a.sort_key - b.sort_key)
);

const cookingOrders = computed(() => 
  orders.value
    .filter(order => order.status === 'cooking')
    .sort((a, b) => a.sort_key - b.sort_key)
);

const getTimeElapsed = (orderDate) => {
  const minutes = Math.floor((new Date() - new Date(orderDate)) / 60000);
  return `${minutes}m ago`;
};

const getOrderStatusClass = (order) => {
  const minutes = Math.floor((new Date() - new Date(order.order_date)) / 60000);
  if (minutes > 20) return 'critical';
  if (minutes > 15) return 'warning';
  return order.status;
};

const getInitialElapsedTime = (order) => {
  return Math.floor((new Date() - new Date(order.order_date)) / 60000);
};

const getCookingElapsedTime = (order) => {
  if (!order.cooking_started_at) return 0;
  return Math.floor((new Date() - new Date(order.cooking_started_at)) / 60000);
};

const updateStatus = async ({ orderId, newStatus, originalDate }) => {
  try {
    const order = orders.value.find(o => o.order_id === orderId);
    if (!order) return;

    // Update local state first
    order.status = newStatus;
    order.sort_key = new Date(originalDate).getTime(); // Preserve original sort order

    // Then send to server
    await axios.post('http://localhost:8000/api/kitchen/update-status', {
      order_id: orderId,
      status: newStatus,
      table_id: order.table_id
    });

    // Instead of fetching all orders, just update the status locally
    orders.value = orders.value.map(o => {
      if (o.order_id === orderId) {
        return {
          ...o,
          status: newStatus,
          sort_key: new Date(originalDate).getTime()
        };
      }
      return o;
    });

    // Re-sort orders to maintain original order
    orders.value.sort((a, b) => a.sort_key - b.sort_key);

  } catch (error) {
    console.error('Error updating order status:', error);
  }
};

const fetchOrders = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/kitchen/pending-orders');
    
    if (!response.data) {
      orders.value = [];
      return;
    }

    // The items are already in the correct format from the backend
    orders.value = response.data;
    orderStore.updateOrders(response.data);
    
  } catch (error) {
    console.error('Error fetching orders:', error);
    orders.value = [];
  }
};

const logout = async () => {
  console.log("Logout initiated from kitchen page");
  
  // Call the auth store's logout method which is more comprehensive
  authStore.logout();
  
  // Clear any remaining auth tokens from localStorage as a backup
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user_role');
  localStorage.removeItem('user');
  
  console.log("Auth data cleared, redirecting to auth page");
  
  // Use await to ensure navigation completes
  await router.push('/auth');
};

onMounted(() => {
  if (import.meta.server) return;
  
  // Comprehensive role checking with debugging
  const authToken = localStorage.getItem('auth_token');
  const userRole = localStorage.getItem('user_role');
  
  console.log('Kitchen page - Auth check:', { 
    hasToken: !!authToken,
    userRole: userRole || 'none'
  });
  
  // Explicit role check with proper error messaging
  if (!authToken) {
    console.log('No auth token found, redirecting to login');
    router.push('/auth');
    return;
  }
  
  if (userRole !== 'kitchen') {
    console.log(`Access denied: Role "${userRole}" cannot access kitchen page`);
    router.push('/auth');
    return;
  }
  
  console.log('Access granted: Kitchen role confirmed');
  
  console.log('Listening for kitchen orders...');
  fetchOrders();
  
  $ws.channel('orders')
    .listen('OrderSent', () => {
      console.log('New order received');
      fetchOrders();
    })
    .listen('OrderStatusChanged', (e) => {
      // Refresh orders for both new orders and status changes
      console.log('Order update received:', e);
      fetchOrders();
    });
});

onBeforeUnmount(() => {
  timeStore.clearAll();
  orderStore.clearAll();
  $ws.leaveChannel('orders');
});

</script>

<style scoped>
.kitchen-dashboard {
  min-height: 100vh;
  background: #f8f9fa;
  padding: 2rem;
}

.dashboard-header {
  background: white;
  border-radius: 16px;
  padding: 1.5rem 2rem;
  margin-bottom: 2rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

h1 {
  font-size: 2.5rem;
  color: #2c3e50;
  margin: 0;
}

.order-stats {
  display: flex;
  gap: 2rem;
}

.stat-item {
  text-align: center;
  padding: 1rem 2rem;
  border-radius: 12px;
  background: #f8f9fa;
  transition: all 0.3s ease;
}

.stat-item.attention {
  background: #fff3cd;
  animation: pulse 2s infinite;
}

.stat-value {
  display: block;
  font-size: 2rem;
  font-weight: 700;
  color: #2c3e50;
}

.stat-label {
  font-size: 1rem;
  color: #7f8c8d;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.orders-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 2rem;
  align-items: start;
}

.orders-column {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.orders-column h2 {
  font-size: 1.5rem;
  color: #2c3e50;
  margin: 0 0 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #f8f9fa;
}

.orders-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* Transitions */
.order-enter-active,
.order-leave-active {
  transition: all 0.5s ease;
}

.order-enter-from {
  opacity: 0;
  transform: translateY(30px);
}

.order-leave-to {
  opacity: 0;
  transform: translateX(-30px);
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); }
}

.header-right {
  display: flex;
  align-items: center;
  gap: 2rem;
}

.logout-button {
  background: linear-gradient(45deg, #e74c3c, #c0392b);
  border: none;
  color: white;
  padding: 0.8rem 1.5rem;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.logout-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
}

@media (max-width: 768px) {
  .kitchen-dashboard {
    padding: 1rem;
  }

  .header-content {
    flex-direction: column;
    gap: 1rem;
  }

  .order-stats {
    width: 100%;
    justify-content: space-between;
  }

  .stat-item {
    padding: 0.8rem;
  }

  .orders-grid {
    grid-template-columns: 1fr;
  }

  .header-right {
    width: 100%;
    flex-direction: column;
    gap: 1rem;
  }
  
  .logout-button {
    width: 100%;
  }
}
</style>
