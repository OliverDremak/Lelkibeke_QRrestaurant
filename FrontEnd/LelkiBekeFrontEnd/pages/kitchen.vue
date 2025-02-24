<template>
  <div class="kitchen-dashboard">
    <header class="dashboard-header">
      <h1>Kitchen Dashboard</h1>
      <div class="status-filters">
        <button 
          v-for="status in ['all', 'pending', 'cooking']"
          :key="status"
          :class="['filter-btn', { active: currentFilter === status }]"
          @click="currentFilter = status"
        >
          {{ status.toUpperCase() }}
        </button>
      </div>
    </header>

    <div class="orders-grid">
      <div class="order-status">
        <h3>Pending Orders</h3>
        <div class="orders-container">
          <div v-for="order in pendingOrders" :key="order.order_id">
            <div class="order-header">
              <span class="order-id">#{{ order.order_id }}</span>
              <span class="order-time">{{ getTimeElapsed(order.order_date) }}</span>
            </div>
            
            <div class="order-items">
              <div v-for="item in order.items" :key="item.id" class="item">
                <span class="quantity">{{ item.quantity }}×</span>
                <span class="name">{{ item.menu_item_name }}</span>
                <span v-if="item.notes" class="notes">{{ item.notes }}</span>
              </div>
            </div>
            
            <div class="status-controls">
              <button @click="updateStatus(order.order_id, 'cooking')">
                Start Cooking
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="order-status">
        <h3>Currently Cooking</h3>
        <div class="orders-container">
          <div v-for="order in cookingOrders" :key="order.order_id">
            <div class="order-header">
              <span class="order-id">#{{ order.order_id }}</span>
              <span class="order-time">{{ getTimeElapsed(order.order_date) }}</span>
            </div>
            
            <div class="order-items">
              <div v-for="item in order.items" :key="item.id" class="item">
                <span class="quantity">{{ item.quantity }}×</span>
                <span class="name">{{ item.menu_item_name }}</span>
                <span v-if="item.notes" class="notes">{{ item.notes }}</span>
              </div>
            </div>
            
            <div class="status-controls">
              <button @click="updateStatus(order.order_id, 'cooked')">
                Mark as Ready
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { useNuxtApp } from '#app';
import axios from 'axios';

export default {
  setup() {
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
      orders.value.filter(order => order.status === 'pending')
    );

    const cookingOrders = computed(() => 
      orders.value.filter(order => order.status === 'cooking')
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

    const updateStatus = async (orderId, status) => {
      try {
        const order = orders.value.find(o => o.order_id === orderId);
        await axios.post('http://localhost:8000/api/kitchen/update-status', {
          order_id: orderId,
          status: status,
          table_id: order.table_id  // Include table_id in request
        });
        await fetchOrders();
      } catch (error) {
        console.error('Error updating order status:', error);
      }
    };

    const formatOrderItems = (order) => {
      if (!order) return [];
      return {
        ...order,
        items: order.items ? order.items.split(', ').map(item => {
          const [quantityPart, ...rest] = item.split('x ');
          const itemText = rest.join('x ');
          const match = itemText.match(/(.*?)(?:\s*\((.*?)\))?$/);
          return {
            quantity: parseInt(quantityPart),
            menu_item_name: match[1].trim(),
            notes: match[2] || ''
          };
        }) : []
      };
    };

    const fetchOrders = async () => {
      try {
        const response = await axios.get('http://localhost:8000/api/kitchen/pending-orders');
        const scrollPosition = window.scrollY;
        orders.value = response.data.map(order => formatOrderItems(order));
        nextTick(() => {
          window.scrollTo(0, scrollPosition);
        });
      } catch (error) {
        console.error('Error fetching orders:', error);
      }
    };

    onMounted(() => {
      if (import.meta.server) return;
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
      $ws.leaveChannel('orders');
    });

    return {
      orders,
      currentFilter,
      pendingOrders,
      cookingOrders,
      getTimeElapsed,
      getOrderStatusClass,
      updateStatus
    };
  }
}
</script>

<style scoped>
.kitchen-dashboard {
  padding: 2rem;
  background: #f8f9fa;
  min-height: 100vh;
}

.dashboard-header {
  margin-bottom: 2rem;
}

.orders-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 2rem;
  contain: paint;  /* Prevents layout shifts */
}

.table-orders {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.order-card {
  margin: 1rem 0;
  padding: 1rem;
  border-radius: 8px;
  border-left: 4px solid;
}

.order-card.pending { border-left-color: #3498db; }
.order-card.cooking { border-left-color: #f39c12; }
.order-card.critical { border-left-color: #e74c3c; }
.order-card.warning { border-left-color: #f1c40f; }

.status-btn {
  width: 100%;
  padding: 0.8rem;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
}

.status-btn.cooking {
  background: #f39c12;
  color: white;
}

.status-btn.cooked {
  background: #2ecc71;
  color: white;
}

.order-status {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  contain: paint;
  height: min-content;
}
</style>
