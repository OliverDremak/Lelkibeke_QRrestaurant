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
        <!-- Simplified Table Number Display -->
        <div class="table-display">
          <div class="table-number-badge">
            <span class="table-prefix">#</span>
            <span class="table-number">{{ tableId }}</span>
          </div>
        </div>
        
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

/* Simplified Table Number Display Styles */
.table-display {
  text-align: center;
  margin: 1.5rem auto 0.5rem; /* Reduced bottom margin from 2rem to 0.5rem */
}

.table-number-badge {
  display: inline-flex;
  align-items: center;
  background: linear-gradient(135deg, #dd6013, #ffbd00);
  border-radius: 50px;
  padding: 0.5rem 1.5rem;
  box-shadow: 0 4px 12px rgba(221, 96, 19, 0.25);
  position: relative;
}

.table-prefix {
  font-size: 1rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.8);
  letter-spacing: 2px;
  margin-right: 8px;
}

.table-number {
  font-size: 2.5rem;
  font-weight: 800;
  color: white;
  line-height: 1;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  position: relative;
}

/* Dark mode support */
:root.dark .table-number-badge {
  box-shadow: 0 4px 12px rgba(255, 189, 0, 0.25);
}
</style>