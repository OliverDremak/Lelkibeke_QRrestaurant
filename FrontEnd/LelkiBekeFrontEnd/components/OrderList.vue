<template>
  <div class="order-list mt-4">
    <div v-if="groupedOrders.length">
      <div v-for="order in groupedOrders" :key="order.order_id" class="card mb-3 border-0 shadow-sm">
        <div class="card-body p-3 d-flex justify-content-between align-items-start">
          <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h5 class="card-title mb-0">Order #{{ order.order_id }}</h5>
              <span class="badge" :class="statusBadgeClass(order.status)">
                {{ order.status }}
              </span>
            </div>
            <p class="card-text text-muted small mb-1">Order Date: {{ order.order_date }}</p>
            <p class="card-text text-muted small mb-2">Total Price: ${{ order.total_price }}</p>
            <p class="card-text small mb-1"><strong>Items:</strong></p>
            <ul class="list-unstyled small mb-2">
              <li v-for="item in order.items" :key="item.menu_item_name" class="mb-1">
                {{ item.menu_item_name }} (x{{ item.quantity }}) - <em v-if="item.notes">{{ item.notes }}</em>
              </li>
            </ul>
          </div>
          <button
            v-if="order.status !== 'done'"
            @click="confirmMarkAsServed(order)"
            class="btn btn-sm btn-success"
          >
            Mark as Served
          </button>
        </div>
      </div>
    </div>
    <div v-else>
      <p class="text-center">No orders for this table.</p>
    </div>

    <!-- Confirmation Modal -->
    <div v-if="showConfirmation" class="modal" tabindex="-1" role="dialog" style="display: block;">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Confirm Action</h5>
            <button type="button" class="close" @click="showConfirmation = false" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to mark this order as served?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showConfirmation = false">Cancel</button>
            <button type="button" class="btn btn-primary" @click="markAsServed">Yes</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch } from 'vue';
import axios from 'axios';

export default {
  props: {
    orders: {
      type: Array,
      required: true,
    },
  },
  setup(props) {
    const showConfirmation = ref(false);
    const selectedOrder = ref(null);

    // Computed property to automatically group orders when props change
    const groupedOrders = computed(() => {
      return props.orders.reduce((acc, order) => {
        const existingOrder = acc.find(o => o.order_id === order.order_id);
        if (existingOrder) {
          existingOrder.items.push({
            menu_item_name: order.menu_item_name,
            quantity: order.quantity,
            notes: order.notes || 'No notes',
          });
        } else {
          acc.push({
            order_id: order.order_id,
            order_date: order.order_date,
            status: order.status,
            total_price: order.total_price,
            items: [{
              menu_item_name: order.menu_item_name,
              quantity: order.quantity,
              notes: order.notes || 'No notes',
            }],
          });
        }
        return acc;
      }, []);
    });

    // Watch for changes to props.orders
    watch(() => props.orders, (newOrders) => {
      console.log("Orders updated:", newOrders);
    }, { deep: true });

    const confirmMarkAsServed = (order) => {
      selectedOrder.value = order;
      showConfirmation.value = true;
    };

    const markAsServed = async () => {
      if (selectedOrder.value) {
        try {
          await axios.post(`http://localhost:8000/api/setOrderStatus/${selectedOrder.value.order_id}/done`);
          showConfirmation.value = false;
        } catch (error) {
          console.error('Error marking order as served:', error);
        }
      }
    };

    const statusBadgeClass = (status) => {
      return {
        'badge-warning': status === 'pending',
        'badge-info': status === 'preparing',
        'badge-success': status === 'done',
      };
    };

    return {
      groupedOrders,
      confirmMarkAsServed,
      markAsServed,
      statusBadgeClass,
      showConfirmation,
    };
  },
};
</script>

<style scoped>
/* Your styles here */
</style>