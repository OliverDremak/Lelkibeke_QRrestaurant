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
  <div class="popular-items p-4 bg-orange-50 rounded-lg shadow my-4">
    <h2 class="text-2xl font-bold mb-4 text-orange-600">Most Popular Items</h2>
    <div v-if="isLoading" class="text-center">
      <span class="loading loading-spinner loading-lg"></span>
    </div>
    <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div v-for="item in popularItems" :key="item.menu_item" 
           class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center space-x-2">
          <span class="text-xl">🏆</span>
          <h3 class="text-lg font-semibold">{{ item.menu_item }}</h3>
        </div>
        <p class="text-gray-600 mt-2">Ordered {{ item.total_sold }} times</p>
      </div>
    </div>
  </div>
</template>
