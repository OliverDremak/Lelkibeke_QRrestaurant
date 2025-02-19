<template>
  <div class="row">
    <div
      v-for="table in tables"
      :key="table.id"
      class="col-md-3 mb-3"
    >
      <div
        :class="['card', 'text-center', 'shadow-sm', 'border-0', table.is_available === 1 ? 'bg-success text-white' : 'bg-danger text-white']"
        style="cursor: pointer;"
        @click="selectTable(table.id)"
      >
        <div class="card-body p-3">
          <h5 class="card-title mb-2">{{ table.table_number }}</h5>
          <p class="card-text small mb-0">
            {{ table.is_available === 1 ? 'Available' : 'Occupied' }}
          </p>
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
import axios from 'axios';

export default {
  props: {
    tables: {
      type: Array,
      required: true,
    },
  },
  methods: {
    async toggleOccupancy(table) {
      const newStatus = table.is_available === 1 ? false : true;
      try {
        await axios.post(`http://localhost:8000/api/setOccupancyStatus/${table.id}/${newStatus}`);
        table.is_available = newStatus;
        this.$emit('refresh-tables');
      } catch (error) {
        console.error('Error setting table occupancy status:', error);
      }
    },
    selectTable(tableId) {
      this.$emit('select-table', tableId);
    },
  },
};
</script>

<style scoped>
.card {
  border-radius: 8px;
}
.btn-light {
  color: black;
}
</style>