<template>
    <div>
      <h2 class="text-center mb-4">Orders for {{ table.table_number }}</h2>
  
      <div v-for="order in orders" :key="order.id" class="card mb-3">
        <div class="card-body">
          <h5 class="card-title">Order ID: {{ order.id }}</h5>
          <p class="card-text">
            <strong>Status:</strong>
            <span :class="{
              'text-warning': order.status === 'pending',
              'text-success': order.status === 'served',
              'text-danger': order.status === 'cancelled'
            }">
              {{ order.status }}
            </span>
          </p>
          <p class="card-text"><strong>Total Price:</strong> ${{ order.total_price }}</p>
          <p class="card-text"><strong>Ordered At:</strong> {{ order.created_at }}</p>
  
          <!-- Order Items -->
          <OrderDetails :order="order" @update-status="updateOrderStatus" />
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import OrderDetails from './OrderDetails.vue';
  
  export default {
    components: {
      OrderDetails,
    },
    props: {
      table: {
        type: Object,
        required: true,
      },
      orders: {
        type: Array,
        required: true,
      },
    },
    methods: {
      updateOrderStatus(orderId, status) {
        this.$emit('update-status', orderId, status);
      },
    },
  };
  </script>