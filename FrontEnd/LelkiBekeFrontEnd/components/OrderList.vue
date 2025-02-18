<template>
  <div class="order-list mt-4">
    <h2 class="text-center mb-4">Orders for Selected Table</h2>
    <div v-if="orders.length">
      <div v-for="order in orders" :key="order.id" class="card mb-3">
        <div class="card-body">
          <h5 class="card-title">Order #{{ order.id }}</h5>
          <p class="card-text">Items:</p>
          <ul>
            <li v-for="item in order.items" :key="item.id">{{ item.name }} - {{ item.quantity }}</li>
          </ul>
        </div>
      </div>
    </div>
    <div v-else>
      <p class="text-center">No orders for this table.</p>
    </div>
  </div>
</template>

<script>
import { ref, watch } from 'vue';

export default {
  props: {
    orders: {
      type: Array,
      required: true,
    },
  },
  setup(props) {
    const orders = ref(props.orders);

    watch(() => props.orders, (newOrders) => {
      orders.value = newOrders;
    });

    return {
      orders,
    };
  },
};
</script>

<style scoped>
.order-list {
  margin-top: 20px;
}
.card {
  margin-bottom: 10px;
}
</style>