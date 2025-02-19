<template>
  <div class="container-fluid p-4">
    <h1 class="text-center mb-4 display-4 text-primary">Waiter Dashboard</h1>

    <!-- Table List -->
    <TableList :tables="tables" @select-table="selectTable" />

    <!-- Selected Table and Orders -->
    <div v-if="selectedTable" class="selected-table mt-4 p-3">
      <h2 class="text-center text-white">Table #{{ selectedTable.table_number }}</h2>
      <OrderList :orders="selectedTableOrders" v-if="selectedTableOrders.length" />
      <p v-else class="text-center text-white">No active orders for this table.</p>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import TableList from '@/components/TableList.vue';
import OrderList from '@/components/OrderList.vue';
import axios from 'axios';

export default {
  components: {
    TableList,
    OrderList,
  },
  setup() {
    const tables = ref([]);
    const selectedTableOrders = ref([]);
    const selectedTable = ref(null);

    const fetchTables = async () => {
      try {
        const response = await axios.get('http://localhost:8000/api/tables');
        tables.value = response.data;
      } catch (error) {
        console.error('Error fetching tables:', error);
      }
    };

    const fetchOrdersForTable = async (tableId) => {
      try {
        const response = await axios.get(`http://localhost:8000/api/ordersByTableId/${tableId}`);
        return response.data;
      } catch (error) {
        console.error('Error fetching orders:', error);
        return [];
      }
    };

    const selectTable = async (table) => {
      selectedTable.value = table;
      selectedTableOrders.value = await fetchOrdersForTable(table.id);
    };

    onMounted(() => {
      fetchTables();
    });

    return {
      tables,
      selectedTableOrders,
      selectedTable,
      selectTable,
    };
  },
};
</script>

<style scoped>
.container-fluid {
  background-color: #222;
  min-height: 100vh;
  color: white;
  font-family: 'Arial', sans-serif;
}

.selected-table {
  background: linear-gradient(135deg, #ff9800, #ff5722);
  border-radius: 12px;
  text-align: center;
  padding: 20px;
}

h1 {
  font-weight: bold;
}
</style>
