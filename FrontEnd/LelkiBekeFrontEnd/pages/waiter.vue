<template>
  <div class="waiter-dashboard">
    <WaiterNotifications ref="notifications" />
    
    <NotificationStack ref="notificationStack" />
    
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
      <button 
        class="action-button"
        :class="{ 'active': showAllOrders }"
        @click="toggleAllOrders"
      >
        {{ showAllOrders ? 'Table View' : 'All Orders' }}
      </button>
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
import { ref, onMounted } from 'vue';
import { useNuxtApp } from '#app';
import TableList from '@/components/TableList.vue';
import OrderList from '@/components/OrderList.vue';
import NotificationStack from '@/components/NotificationStack.vue';
import axios from 'axios';

const tables = ref([]);
const selectedTable = ref(null);
const selectedTableOrders = ref([]);
const notificationStack = ref(null);
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

const refreshOrders = async () => {
  if (selectedTable.value) {
    await fetchActiveOrdersForTable(selectedTable.value.id);
  }
};

const fetchActiveOrdersForTable = async (tableId) => {
  try {
    const response = await axios.post('http://localhost:8000/api/getActiveOrdersForTable', {
      id: tableId
    });
    console.log('Fetched table orders:', response.data); // Add this debug log
    selectedTableOrders.value = response.data;
  } catch (error) {
    console.error('Error fetching active orders for table:', error);
  }
};

const selectTable = async (tableId) => {
  selectedTable.value = tables.value.find(t => t.id === tableId);
  if (selectedTable.value) {
    showAllOrders.value = false; // Switch to table view when selecting a table
    await fetchActiveOrdersForTable(tableId);
    
    // Scroll to orders section with smooth animation
    setTimeout(() => {
      ordersSection.value?.scrollIntoView({ 
        behavior: 'smooth',
        block: 'start'
      });
    }, 100); // Small delay to ensure content is rendered
  }
};

const toggleAllOrders = async () => {
  showAllOrders.value = !showAllOrders.value;
  if (showAllOrders.value) {
    await fetchAllOrders();
    // Wait for orders to load then scroll
    setTimeout(() => {
      ordersSection.value?.scrollIntoView({ 
        behavior: 'smooth',
        block: 'start'
      });
    }, 100);
  }
};

const fetchAllOrders = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/allActiveOrders');
    console.log('Fetched orders:', response.data); // Add this debug log
    allOrders.value = response.data;
  } catch (error) {
    console.error('Error fetching all orders:', error);
  }
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

onMounted(() => {
  fetchTables();
  fetchAllOrders(); // Fetch initial orders
  
  const { $ws } = useNuxtApp();
  $ws.channel('orders')
    .listen('OrderSent', (e) => {
      if (notificationStack.value) {
        notificationStack.value.addNotification(e.tableId);
        // Refresh orders without scrolling
        if (showAllOrders.value) {
          fetchAllOrders();
        }
        if (selectedTable.value?.id === e.tableId) {
          fetchActiveOrdersForTable(e.tableId);
        }
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
}

.orders-container {
  background: white;
  border-radius: 15px;
  padding: 2rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  scroll-margin-top: 2rem; /* Adds some space at the top when scrolling */
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

@media (max-width: 768px) {
  .waiter-dashboard {
    padding: 1rem;
  }

  .dashboard-header {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }

  .action-button {
    width: 100%;
  }
}
</style>