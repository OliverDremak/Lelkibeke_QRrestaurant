<template>
  <div class="container mt-5">
    <h1 class="text-center mb-4">Waiter Dashboard</h1>

    <!-- Table List -->
    <TableList :tables="tables" @select-table="selectTable" />

    <!-- Selected Table and Orders -->
    <div v-if="selectedTable">
      <h2 class="text-center">Selected Table: {{ selectedTable.id }}</h2>
      <OrderList :orders="selectedTableOrders" v-if="selectedTableOrders.length" />
      <p v-else class="text-center">No orders active for this table.</p>
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
      console.log('Selected Table:', selectedTable.value);
      selectedTableOrders.value = await fetchOrdersForTable(table.id);
      console.log('Selected Table Orders:', selectedTableOrders.value);
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
/* Your styles here */
</style>