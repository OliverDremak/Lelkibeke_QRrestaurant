<template>
    <div class="row">
      <div
        v-for="table in tables"
        :key="table.id"
        class="col-md-3 mb-3"
      >
        <div
          :class="['card', 'text-center', { 'bg-danger text-white': !table.is_available }]"
          @click="$emit('select-table', table.id)"
          style="cursor: pointer;"
        >
          <div class="card-body">
            <h5 class="card-title">{{ table.table_number }}</h5>
            <p class="card-text">
              {{ table.is_available ? 'Available' : 'Occupied' }}
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
  </style>