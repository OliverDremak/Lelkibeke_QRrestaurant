<template>
    <div>
      <ul class="list-group list-group-flush">
        <li
          v-for="item in order.order_items"
          :key="item.id"
          class="list-group-item"
        >
          <strong>{{ item.menu_item.name }}</strong> (x{{ item.quantity }}) - ${{ item.menu_item.price }}
          <br>
          <small>{{ item.notes }}</small>
        </li>
      </ul>
  
      <!-- Update Order Status -->
      <div class="mt-3">
        <button
          class="btn btn-primary btn-sm"
          @click="$emit('update-status', order.id, 'served')"
          :disabled="order.status === 'served'"
        >
          Mark as Served
        </button>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    props: {
      order: {
        type: Object,
        required: true,
      },
    },
  };
  </script>