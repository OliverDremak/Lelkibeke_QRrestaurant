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
        {{ t('orderDetails.served') }}
        </button>
      </div>
    </div>
  </template>
  
  <script>
  import { useI18n } from '#imports'; // Import the useI18n function
  
  export default {
    props: {
      order: {
        type: Object,
        required: true,
      },
    },
    setup() {
      const { t } = useI18n(); // Destructure the t function from useI18n
  
      // You can now use the `t` function for translations
      const translatedText = t('some.translation.key'); // Example usage
  
      // Return the t function and any other reactive data you need
      return {
        t,
        translatedText,
      };
    },
  };
  </script>