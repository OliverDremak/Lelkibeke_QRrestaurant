<template>
    <div class="container mt-5">
      <h1 class="text-center mb-4">Waiter Dashboard</h1>
  
      <!-- Table List -->
      <TableList :tables="tables" @select-table="selectTable" />
  
      <!-- Orders for Selected Table -->
      <div v-if="selectedTable" class="mt-4">
        <OrderList
          :table="selectedTable"
          :orders="orders"
          @update-status="updateOrderStatus"
        />
      </div>
    </div>
  </template>
  
  <script>
  import TableList from '@/components/TableList.vue';
  import OrderList from '@/components/OrderList.vue';
  
  export default {
    components: {
      TableList,
      OrderList,
    },
    data() {
      return {
        // Static data for testing
        tables: [
          { id: 1, table_number: 'Table 1', is_available: false },
          { id: 2, table_number: 'Table 2', is_available: true },
          { id: 3, table_number: 'Table 3', is_available: false },
        ],
        selectedTable: null,
        orders: [
          {
            id: 1,
            table_id: 1,
            status: 'pending',
            total_price: '50.00',
            created_at: '2023-10-01 12:00:00',
            order_items: [
              {
                id: 1,
                menu_item_id: 1,
                quantity: 2,
                notes: 'Extra cheese',
                menu_item: {
                  name: 'Pizza',
                  price: '10.00',
                  description: 'Delicious pizza',
                },
              },
              {
                id: 2,
                menu_item_id: 2,
                quantity: 1,
                notes: 'No onions',
                menu_item: {
                  name: 'Burger',
                  price: '8.00',
                  description: 'Juicy burger',
                },
              },
            ],
          },
          {
            id: 2,
            table_id: 1,
            status: 'served',
            total_price: '30.00',
            created_at: '2023-10-01 11:30:00',
            order_items: [
              {
                id: 3,
                menu_item_id: 3,
                quantity: 1,
                notes: 'Extra sauce',
                menu_item: {
                  name: 'Pasta',
                  price: '12.00',
                  description: 'Creamy pasta',
                },
              },
            ],
          },
        ],
      };
    },
    methods: {
      selectTable(tableId) {
        this.selectedTable = this.tables.find(table => table.id === tableId);
      },
      updateOrderStatus(orderId, status) {
        const order = this.orders.find(order => order.id === orderId);
        if (order) {
          order.status = status;
        }
      },
    },
  };
  </script>
  
  <style>
  /* Custom CSS */
  .text-warning {
    color: #ffc107 !important;
  }
  .text-success {
    color: #28a745 !important;
  }
  .text-danger {
    color: #dc3545 !important;
  }
  </style>