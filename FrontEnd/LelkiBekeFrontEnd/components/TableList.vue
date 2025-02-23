<template>
  <div class="row">
    <div
      v-for="table in tables"
      :key="table.id"
      class="col-md-3 mb-3"
    >
      <div
        :class="[
          'card', 'text-center', 'shadow-sm', 'border-0',
          table.is_available === 1 ? 'bg-success text-white' : 'bg-danger text-white',
          { 'bg-warning': scannedTableIds.includes(table.id) }
        ]"
        style="cursor: pointer;"
        @click="selectTable(table.id)"
      >
        <div class="card-body p-3">
          <h5 class="card-title mb-2">{{ table.table_number }}</h5>
          <p class="card-text small mb-0">
            {{ table.is_available === 1 ? 'Available' : 'Occupied' }}
          </p>
          <div v-if="scannedTableIds.includes(table.id)" class="mt-2">
            <span class="badge bg-light text-dark">QR Scanned</span>
          </div>
          <button
            class="btn btn-sm btn-light mt-2"
            @click.stop="toggleOccupancy(table)"
          >
            {{ table.is_available === 1 ? 'Set Occupied' : 'Set Available' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useNuxtApp } from '#app';
import axios from 'axios';

export default {
  props: {
    tables: {
      type: Array,
      required: true,
    },
  },
  setup(props, { emit }) {
    const { $ws } = useNuxtApp();
    const scannedTableIds = ref([]);

    const toggleOccupancy = async (table) => {
      console.log('Toggling occupancy status for table:', table);
      const newStatus = table.is_available === 1 ? false : true;
      try {
        await axios.post(`http://localhost:8000/api/setOccupancyStatus`, {
          id: table.id,
          is_available: newStatus
        });
        table.is_available = newStatus;
        emit('refresh-tables');
      } catch (error) {
        console.error('Error setting table occupancy status:', error);
      }
    };

    const selectTable = (tableId) => {
      emit('select-table', tableId);
    };

    const removeScannedTable = (tableId) => {
      const index = scannedTableIds.value.indexOf(tableId);
      if (index > -1) {
        scannedTableIds.value.splice(index, 1);
      }
    };

    onMounted(() => {
      if (import.meta.server) return;
      console.log('Listening for table events...');
      $ws.channel('tables')
        .listen('TableScanned', (e) => {
          console.log('Table scan event received:', e);
          // Convert tableId to number to ensure consistent comparison
          const receivedTableId = parseInt(e.tableId);
          const scannedTable = props.tables.find((table) => table.id === receivedTableId);
          console.log('Matched table:', scannedTable);
          
          if (scannedTable && scannedTable.is_available === 1) {
            // Add table to scanned tables if not already present
            if (!scannedTableIds.value.includes(receivedTableId)) {
              scannedTableIds.value.push(receivedTableId);
              
              // Remove this specific table from scanned tables after 1 minute
              setTimeout(() => {
                removeScannedTable(receivedTableId);
              }, 60000);
            }
          }
        });
      $ws.channel('orders')
        .listen('OrderSent', (e) => {
          console.log('Order received for table ID:', e.tableId);
          selectTable(e.tableId); // This will trigger parent component to fetch orders
        });
    });

    onBeforeUnmount(() => {
      $ws.leaveChannel('tables');
    });

    return {
      scannedTableIds,
      toggleOccupancy,
      selectTable,
    };
  },
};
</script>

<style scoped>
.card {
  border-radius: 8px;
  transition: background-color 0.3s ease;
}

.btn-light {
  color: black;
}

.bg-warning {
  background-color: #ffc107 !important;
  color: black;
}

.badge {
  font-size: 0.8rem;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
}
</style>