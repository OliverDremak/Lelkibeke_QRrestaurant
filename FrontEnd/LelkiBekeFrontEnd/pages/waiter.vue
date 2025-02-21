<template>
  <div class="container mt-5">
    <h1 class="text-center mb-4">Waiter Dashboard</h1>

      <!-- Table List -->
      <TableList :tables="tables" @select-table="selectTable" @refresh-tables="fetchTables" />

      <!-- Selected Table Details -->
      <div v-if="selectedTable" class="mt-4">
        <h2 class="text-center mb-4">Selected Table: {{ selectedTable.table_number }}</h2>
        <p class="text-center">{{ selectedTable.is_available === 1 ? 'Available' : 'Occupied' }}</p>
      </div>

      <!-- Orders for Selected Table -->
      <OrderList :orders="selectedTableOrders" v-if="selectedTableOrders.length"/>
      <p v-else class="text-center">No orders for this table.</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useNuxtApp } from '#app';
import TableList from '@/components/TableList.vue';
import OrderList from '@/components/OrderList.vue';
import axios from 'axios';

const tables = ref([]);
const selectedTable = ref(null);
const selectedTableOrders = ref([]);

const fetchTables = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/tables');
    tables.value = response.data;
  } catch (error) {
    console.error('Error fetching tables:', error);
  }
};

const selectTable = async (tableId) => {
  try {
    const table = tables.value.find(t => t.id === tableId);
    selectedTable.value = table;
    const response = await axios.get(`http://localhost:8000/api/ordersByTableId/${tableId}`);
    selectedTableOrders.value = response.data;
  } catch (error) {
    console.error('Error fetching orders for table:', error);
  }
};

onMounted(() => {
  fetchTables();
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
</style>