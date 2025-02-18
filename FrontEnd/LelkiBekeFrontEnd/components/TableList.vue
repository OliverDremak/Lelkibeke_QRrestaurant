<template>
  <div class="row">
    <div
      v-for="table in tables"
      :key="table.id"
      class="col-md-3 mb-3"
    >
      <div
        :class="['card', 'text-center', table.is_avalable === 1 ? 'bg-success text-white' : 'bg-danger text-white']"
        @click="$emit('select-table', table)" 
        style="cursor: pointer;"
      >
        <div class="card-body">
          <h5 class="card-title">{{ table.table_number }}</h5>
          <p class="card-text">
            {{ table.is_avalable === 1 ? 'Available' : 'Occupied' }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    tables: {
      type: Array,
      required: true,
    },
  },
};
</script>

<style scoped>
.card {
  transition: transform 0.2s;
}

.card:hover {
  transform: scale(1.05);
}

.bg-success {
  background-color: #28a745 !important;
}

.bg-danger {
  background-color: #dc3545 !important;
}
</style>