<template>
  <div>
    <div v-if="error">
      <error-page error-message="Invalid URL" />
    </div>
    <div v-else-if="loading" class="text-center mt-5">
      <p>Loading... Please wait</p>
    </div>
    <div v-else>
      <Navbar />
      <div class="container mt-4">
        <h1 class="text-center">Table {{ tableId }}</h1>
        <RestaurantMenu :table-id="tableId"/>
      </div>
      <Footer/>
    </div>
  </div>
</template>

<script setup>
import { useRoute, useRouter } from 'vue-router';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import ErrorPage from '~/components/error-page.vue';

const route = useRoute();
const router = useRouter();
const loading = ref(true);
const error = ref(false);
const rawId = route.params.id;
const tableId = /^\d+$/.test(rawId) ? parseInt(rawId, 10) : null;

onMounted(async () => {
  try {
    if (tableId === null) {
      error.value = true;
      return;
    }

    const tablesResponse = await axios.get('https://api.innerpeace.jedlik.cloud/api/tables');
    const tableExists = tablesResponse.data.some(table => table.id === tableId);
    
    if (!tableExists) {
      error.value = true;
      return;
    }
    
    await axios.post('https://api.innerpeace.jedlik.cloud/api/table-scanned', {
      tableId: tableId
    });
    
  } catch (err) {
    error.value = true;
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.alert {
  margin: 20px 0;
  padding: 15px;
  border-radius: 4px;
}

.alert-info {
  background-color: #e3f2fd;
  border: 1px solid #bbdefb;
  color: #0d47a1;
}

pre {
  white-space: pre-wrap;
  word-wrap: break-word;
}
</style>