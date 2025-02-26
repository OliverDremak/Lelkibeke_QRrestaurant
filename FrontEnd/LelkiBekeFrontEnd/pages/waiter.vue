<template>
  <div class="waiter-dashboard">
    
    <!-- Scroll to top button -->
    <transition name="fade">
      <button 
        v-show="showScrollTop"
        class="scroll-top-button"
        @click="scrollToTop"
      >
        Tables ▲
      </button>
    </transition>

    <header class="dashboard-header">
      <h1>Waiter Dashboard</h1>
      <div class="header-actions">
        <button 
          class="action-button"
          :class="{ 'active': showAllOrders }"
          @click="toggleAllOrders"
        >
          {{ showAllOrders ? 'Table View' : 'All Orders' }}
        </button>
        <button 
          class="logout-button"
          @click="logout"
        >
          Logout
        </button>
      </div>
    </header>

    <div class="dashboard-content">
      <TableList 
        :tables="tables" 
        :selectedTableId="showAllOrders ? null : selectedTable?.id"
        @select-table="selectTable" 
        @refresh-tables="fetchTables"
        :class="{ 'fade-content': showAllOrders }" 
      />

      <div class="orders-container" ref="ordersSection">
        <h2 class="section-title">{{ getOrdersSectionTitle() }}</h2>
        
        <OrderList 
          v-if="showAllOrders"
          :orders="allOrders" 
          :show-table-info="true"
          @order-updated="fetchAllOrders"
        />
        <OrderList 
          v-else-if="selectedTableOrders.length"
          :orders="selectedTableOrders" 
          @order-updated="refreshOrders"
        />
        <div v-else class="empty-state">
          {{ getNoOrdersMessage() }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick, onBeforeUnmount } from 'vue';
import { useNuxtApp, useRouter } from '#app';
import { useOrderStore } from '@/stores/orderStore';
import TableList from '@/components/TableList.vue';
import OrderList from '@/components/OrderList.vue';
import axios from 'axios';

const router = useRouter();
const orderStore = useOrderStore();

const tables = ref([]);
const selectedTable = ref(null);
const selectedTableOrders = ref([]);
const showAllOrders = ref(false);
const allOrders = ref([]);
const ordersSection = ref(null);
const showScrollTop = ref(false);
const tableListTop = ref(0);

const fetchTables = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/tables');
    tables.value = response.data;
  } catch (error) {
    console.error('Error fetching tables:', error);
  }
};

// Separate fetch function without scrolling
const updateTableOrders = async (tableId) => {
  try {
    const response = await axios.post('http://localhost:8000/api/getActiveOrdersForTable', {
      id: tableId
    });
    selectedTableOrders.value = response.data;
  } catch (error) {
    console.error('Error fetching active orders for table:', error);
  }
};

// Only use scrolling when explicitly selecting a table
const selectTable = async (tableId) => {
  console.log('Select table clicked:', tableId);
  selectedTable.value = tables.value.find(t => t.id === tableId);
  if (selectedTable.value) {
    showAllOrders.value = false;
    await fetchActiveOrdersForTable(tableId);
    
    // Use same delay as toggleAllOrders
    setTimeout(() => {
      const element = document.querySelector('.orders-container');
      if (element) {
        element.scrollIntoView({ 
          behavior: 'smooth',
          block: 'start'
        });
      }
    }, 200);
  }
};

// Update the refreshOrders function to use the non-scrolling version
const refreshOrders = async () => {
  if (selectedTable.value) {
    await updateTableOrders(selectedTable.value.id);
  }
};

const fetchActiveOrdersForTable = async (tableId) => {
  try {
    const response = await axios.post('http://localhost:8000/api/getActiveOrdersForTable', {
      id: tableId
    });
    
    // Process orders to ensure consistent format
    const processedOrders = response.data.map(order => {
      let parsedItems;
      try {
        // Handle different item formats
        if (typeof order.items === 'string') {
          parsedItems = JSON.parse(order.items);
        } else if (Array.isArray(order.items)) {
          parsedItems = order.items;
        } else {
          parsedItems = [];
        }
      } catch (e) {
        console.error('Error parsing items for order:', order.order_id, e);
        parsedItems = [];
      }

      const processedOrder = {
        order_id: order.order_id,
        table_id: order.table_id,
        order_date: order.order_date,
        status: order.status,
        total_price: order.total_price,
        items: parsedItems
      };

      // Initialize timer tracking for this order
      orderStore.initialize();
      orderStore.timeStore.startTracking(processedOrder.order_id, processedOrder.order_date);

      return processedOrder;
    });

    selectedTableOrders.value = processedOrders;
    
  } catch (error) {
    console.error('Error fetching active orders for table:', error);
    selectedTableOrders.value = [];
  }
};

// Remove or modify automatic scrolling in other functions
const toggleAllOrders = async () => {
  showAllOrders.value = !showAllOrders.value;
  if (showAllOrders.value) {
    await fetchAllOrders();
    // Add controlled scrolling with delay for render
    setTimeout(() => {
      const element = document.querySelector('.orders-container');
      if (element) {
        element.scrollIntoView({ 
          behavior: 'smooth',
          block: 'start'
        });
      }
    }, 200);
  } else if (selectedTable.value) {
    await fetchActiveOrdersForTable(selectedTable.value.id);
  }
};

// Add helper function to manage scrolling
const scrollToOrders = () => {
  setTimeout(() => {
    const element = document.querySelector('.orders-container');
    if (element) {
      element.scrollIntoView({ 
        behavior: 'smooth',
        block: 'start'
      });
    }
  }, 200);
};

const fetchAllOrders = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/allActiveOrders');
    
    // Process the orders to group items by order_id
    const processedOrders = response.data.reduce((acc, item) => {
      if (!acc[item.order_id]) {
        acc[item.order_id] = {
          order_id: item.order_id,
          table_id: item.table_id,
          order_date: item.order_date,
          status: item.status,
          total_price: item.total_price,
          items: []
        };
        // Initialize timer for each new order
        orderStore.initialize();
        orderStore.timeStore.startTracking(item.order_id, item.order_date);
      }
      
      acc[item.order_id].items.push({
        quantity: item.quantity,
        menu_item_name: item.menu_item_name,
        notes: item.notes
      });
      
      return acc;
    }, {});

    // Convert to array
    const formattedOrders = Object.values(processedOrders);
    
    // Update store
    orderStore.updateOrders(formattedOrders);
    allOrders.value = formattedOrders;
    
  } catch (error) {
    console.error('Error fetching all orders:', error);
    allOrders.value = [];
  }
};

const getOrderTime = (orderId) => {
  return orderStore.getElapsedTime(orderId);
};

const getOrdersSectionTitle = () => {
  if (showAllOrders.value) {
    return 'All Active Orders';
  }
  return selectedTable.value 
    ? `Selected Table: ${selectedTable.value.table_number}`
    : 'Selected Table: None';
};

const getNoOrdersMessage = () => {
  if (!selectedTable.value) {
    return 'No table selected';
  }
  return 'No orders for selected table';
};

const scrollToTop = () => {
  window.scrollTo({
    top: 0,
    behavior: 'smooth'
  });
};

const logout = () => {
  // Clear any auth tokens from localStorage
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user_role');
  
  // Redirect to auth page
  router.push('/auth');
};

// Modify the websocket listener to ensure immediate updates
onMounted(() => {
  // Comprehensive role checking with debugging
  const authToken = localStorage.getItem('auth_token');
  const userRole = localStorage.getItem('user_role');
  
  console.log('Waiter page - Auth check:', { 
    hasToken: !!authToken,
    userRole: userRole || 'none'
  });
  
  // Explicit role check with proper error messaging
  if (!authToken) {
    console.log('No auth token found, redirecting to login');
    router.push('/auth');
    return;
  }
  
  if (userRole !== 'waiter') {
    console.log(`Access denied: Role "${userRole}" cannot access waiter page`);
    router.push('/auth');
    return;
  }
  
  console.log('Access granted: Waiter role confirmed');
  
  fetchTables();
  fetchAllOrders();
  
  const { $ws } = useNuxtApp();
  $ws.channel('orders')
    .listen('OrderSent', async (e) => {
      console.log('New order received:', e);
      if (showAllOrders.value) {
        await fetchAllOrders();
      } else if (selectedTable.value && selectedTable.value.id === e.tableId) {
        // For new orders, immediately add to store and update view
        const response = await axios.post('http://localhost:8000/api/getActiveOrdersForTable', {
          id: selectedTable.value.id
        });
        
        const processedOrders = response.data.map(order => ({
          ...order,
          items: typeof order.items === 'string' ? JSON.parse(order.items) : order.items
        }));
        
        // Update store and view
        processedOrders.forEach(order => orderStore.addNewOrder(order));
        selectedTableOrders.value = processedOrders;
      }
    })
    .listen('OrderStatusChanged', async () => {
      if (showAllOrders.value) {
        await fetchAllOrders();
      } else if (selectedTable.value) {
        await updateTableOrders(selectedTable.value.id);
      }
    });

  // Get initial position of table list
  const tableList = document.querySelector('.table-grid');
  if (tableList) {
    tableListTop.value = tableList.offsetTop;
  }

  // Add scroll listener
  window.addEventListener('scroll', () => {
    showScrollTop.value = window.scrollY > tableListTop.value + 200; // Show after scrolling past tables
  });
});

onBeforeUnmount(() => {
  orderStore.clearAll();
});
</script>

<style scoped>
.waiter-dashboard {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  min-height: 100vh;
  padding: 2rem;
}

.dashboard-header {
  background: white;
  padding: 1.5rem 2rem;
  border-radius: 15px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  margin-bottom: 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.dashboard-header h1 {
  color: #2c3e50;
  font-size: 2.5rem;
  margin: 0;
  background: linear-gradient(45deg, #2c3e50, #3498db);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.action-button {
  background: linear-gradient(45deg, #3498db, #2ecc71);
  border: none;
  color: white;
  padding: 1rem 2rem;
  border-radius: 12px;
  font-size: 1.2rem;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 200px;
}

.action-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(46, 204, 113, 0.2);
}

.action-button.active {
  background: linear-gradient(45deg, #2ecc71, #3498db);
}

.dashboard-content {
  display: flex;
  flex-direction: column;
  gap: 2rem;
  contain: paint;
}

.orders-container {
  background: white;
  border-radius: 15px;
  padding: 2rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  scroll-margin-top: 2rem; /* Adds some space at the top when scrolling */
  contain: paint;
}

.section-title {
  color: #2c3e50;
  font-size: 1.8rem;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #e9ecef;
}

.empty-state {
  text-align: center;
  padding: 3rem;
  color: #6c757d;
  font-size: 1.2rem;
  background: #f8f9fa;
  border-radius: 12px;
  border: 2px dashed #dee2e6;
}

.fade-content {
  opacity: 0.7;
  transition: opacity 0.3s ease;
}

.scroll-top-button {
  position: fixed;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  background: linear-gradient(45deg, #3498db, #2ecc71);
  color: white;
  border: none;
  padding: 0.8rem 1.5rem;
  border-radius: 25px;
  cursor: pointer;
  font-weight: 600;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transition: all 0.3s ease;
  z-index: 100;
}

.scroll-top-button:hover {
  transform: translateX(-50%) translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}

.scroll-top-button:active {
  transform: translateX(-50%) translateY(0);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s, transform 0.3s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateX(-50%) translateY(-20px);
}

.header-actions {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.logout-button {
  background: linear-gradient(45deg, #e74c3c, #c0392b);
  border: none;
  color: white;
  padding: 1rem 2rem;
  border-radius: 12px;
  font-size: 1.2rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.logout-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
}

@media (max-width: 768px) {
  .waiter-dashboard {
    padding: 1rem;
  }

  .dashboard-header {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }

  .header-actions {
    width: 100%;
    flex-direction: column;
  }

  .action-button, .logout-button {
    width: 100%;
  }
}

/* Ensure other elements don't interfere with controlled scrolling */
* {
  scroll-behavior: unset !important;
}
</style>