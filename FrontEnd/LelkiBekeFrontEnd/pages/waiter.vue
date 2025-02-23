<template>
  <div class="container mt-5">
    <!-- Make sure NotificationStack is properly instantiated -->
    <NotificationStack ref="notificationStack"></NotificationStack>
    <h1 class="text-center mb-4">Waiter Dashboard</h1>
    
    <!-- Show All Orders Button -->
    <div class="text-center mb-4">
      <button class="btn btn-primary btn-lg w-75" @click="toggleAllOrders">
        {{ showAllOrders ? 'Show Table Orders' : 'Show All Orders' }}
      </button>
    </div>

    <!-- Table List -->
    <TableList :tables="tables" @select-table="selectTable" @refresh-tables="fetchTables" />

    <!-- Orders Section Header -->
    <div class="mt-4 mb-4">
      <h2 class="text-center">
        {{ getOrdersSectionTitle() }}
      </h2>
    </div>

    <!-- Orders Content -->
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
    <p v-else class="text-center text-muted">
      {{ getNoOrdersMessage() }}
    </p>
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
  }
};

const toggleAllOrders = async () => {
  showAllOrders.value = !showAllOrders.value;
  if (showAllOrders.value) {
    await fetchAllOrders();
  }
};

const fetchAllOrders = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/allActiveOrders');
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

onMounted(() => {
  fetchTables();
  fetchAllOrders(); // Fetch initial orders
  
  const { $ws } = useNuxtApp();
  $ws.channel('orders')
    .listen('OrderSent', (e) => {
      console.log('New order received for table:', e.tableId);
      // Make sure notificationStack ref exists
      if (notificationStack.value) {
        notificationStack.value.addNotification(`New order for Table ${e.tableId}!`);
        // Also refresh orders if we're in all orders view
        if (showAllOrders.value) {
          fetchAllOrders();
        }
        // Refresh selected table orders if it matches
        if (selectedTable.value && selectedTable.value.id === e.tableId) {
          fetchActiveOrdersForTable(e.tableId);
        }
      } else {
        console.error('Notification stack reference not found');
      }
    });
});
</script>

<style scoped>
.container {
  margin-top: 20px;
}

/* Animation for notification */
.fixed {
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
  }
  to {
    transform: translateX(0);
  }
}

.btn-lg {
  padding: 1rem;
  font-size: 1.25rem;
  margin-bottom: 2rem;
}

h2 {
  font-size: 1.5rem;
  color: #666;
}

.text-muted {
  font-size: 1.1rem;
}
</style>