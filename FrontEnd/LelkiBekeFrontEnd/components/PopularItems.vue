<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'

const popularItems = ref([])
const isLoading = ref(true)

const fetchPopularItems = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/salesTop-items')
    popularItems.value = response.data.slice(0, 3) // Get only top 3
    isLoading.value = false
  } catch (error) {
    console.error('Error fetching popular items:', error)
    isLoading.value = false
  }
}

onMounted(() => {
  fetchPopularItems()
})
</script>

<template>
  <div class="chunky-container container">
    <h2 class="chunky-title">Most Popular Items</h2>
    <div v-if="isLoading" class="text-center">
      <span class="loading loading-spinner loading-lg"></span>
    </div>
    <div v-else class="row g-4">
      <div v-for="(item, index) in popularItems" 
           :key="item.menu_item" 
           class="col-12 col-sm-6 col-lg-4"
           :style="{ '--delay': `${index * 0.2}s` }">
        <div class="chunky-item h-100">
          <div class="chunky-content">
            <div class="chunky-number">#{{'0' + (index + 1)}}</div>
            <h3 class="chunky-name">{{ item.menu_item }}</h3>
            <div class="chunky-sales">{{ item.menu_item_desc}} orders</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.chunky-container {
  padding: 2rem;
}

.chunky-title {
  font-size: 2.5rem;
  font-weight: 900;
  text-transform: uppercase;
  color: #333;
  text-align: center;
  text-shadow: 
    2px 2px 0 #ff6b6b,
    4px 4px 0 #666;
  margin-bottom: 2rem;
}

.chunky-grid {
  display: block; /* Remove the custom grid */
}

.chunky-item {
  background: white;
  border: 8px solid #333;
  border-radius: 15px;
  position: relative;
  transform: translateY(50px);
  opacity: 0;
  animation: chunkIn 0.5s forwards;
  animation-delay: var(--delay);
  transition: transform 0.3s, border-color 0.3s;
}

.chunky-content {
  padding: 1.5rem;
  background: white;
  border-radius: 7px;
  position: relative;
  transition: transform 0.3s;
}

.chunky-item:hover {
  transform: translate(-8px, -8px);
  border-color: #ff6b6b;
  box-shadow: 8px 8px 0 #333;
}

.chunky-item:hover .chunky-content {
  transform: none;
}

.chunky-number {
  position: absolute;
  top: -20px;
  right: -20px;
  background: #ff6b6b;
  color: white;
  padding: 0.5rem 1rem;
  border: 5px solid #333;
  border-radius: 8px;
  font-weight: 900;
  font-size: 1.2rem;
}

.chunky-name {
  font-size: 1.8rem;
  font-weight: 800;
  color: #333;
  margin-bottom: 0.5rem;
}

.chunky-sales {
  font-size: 1.2rem;
  color: #666;
  font-weight: 600;
}

@keyframes chunkIn {
  to {
    transform: translateY(0);
    opacity: 1;
  }
}
</style>
