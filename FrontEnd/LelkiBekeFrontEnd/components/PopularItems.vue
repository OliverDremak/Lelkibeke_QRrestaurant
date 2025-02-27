<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'

import { useI18n } from '#imports'
const { t } = useI18n()

const popularItems = ref([])
const isLoading = ref(true)

const fetchPopularItems = async () => {
  try {
    const response = await axios.get('https://api.innerpeace.jedlik.cloud/api/salesTop-items')
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
    <h2 class="chunky-title">{{ t('popularItems.title') }}</h2>
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
            <div class="chunky-sales">{{ item.menu_item_desc}}</div>
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
  color: #dd6013;
  text-align: center;
  margin-bottom: 2rem;
  text-shadow: 2px 2px 4px rgba(221, 96, 19, 0.2);
}

.chunky-item {
  background: white;
  border: 3px solid #dd6013;
  border-radius: 15px;
  position: relative;
  transform: translateY(50px);
  opacity: 0;
  animation: chunkIn 0.5s forwards;
  animation-delay: var(--delay);
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(221, 96, 19, 0.1);
  margin-top: 35px;
  overflow: visible;
}

:root.dark .chunky-item {
  background: #1a1a1a;
  border-color: #dd6013;
}

:root.dark .chunky-content {
  background: linear-gradient(45deg, rgba(255, 189, 0, 0.1), rgba(221, 96, 19, 0.1));
}

:root.dark .chunky-name {
  color: #dd6013;
}

:root.dark .chunky-sales {
  color: #ffbd00;
}

:root.dark .chunky-title {
  color: #dd6013;
  text-shadow: 2px 2px 4px rgba(221, 96, 19, 0.4);
}

.chunky-content {
  padding: 1.5rem;
  background: linear-gradient(45deg, rgba(255, 189, 0, 0.05), rgba(221, 96, 19, 0.05));
  border-radius: 12px;
  position: relative;
  transition: all 0.3s ease;
  min-height: 150px;
  height: 100%; /* Add this to ensure full height */
  display: flex;
  flex-direction: column;
  justify-content: flex-start; /* Change from center to flex-start */
}

.chunky-item:hover {
  transform: translateY(-5px);
  border-color: #ffbd00;
  box-shadow: 0 8px 25px rgba(221, 96, 19, 0.2);
}

.chunky-number {
  position: absolute;
  top: -35px;
  left: 50%;
  transform: translateX(-50%);
  background: linear-gradient(45deg, #dd6013, #ffbd00);
  color: white;
  padding: 0.5rem 1.5rem;
  border-radius: 12px;
  font-weight: 900;
  font-size: 1.2rem;
  box-shadow: 0 4px 15px rgba(221, 96, 19, 0.2);
  white-space: nowrap;
  z-index: 2;
}

.chunky-name {
  font-size: 1.8rem;
  font-weight: 800;
  color: #dd6013;
  margin-bottom: 0.5rem;
  transition: all 0.3s ease;
  margin-top: 0.5rem; /* Add some top margin */
}

.chunky-sales {
  font-size: 1.2rem;
  color: #ffbd00;
  font-weight: 600;
}

@keyframes chunkIn {
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

@media (max-width: 768px) {
  .chunky-title {
    font-size: 2rem;
  }
  
  .chunky-name {
    font-size: 1.5rem;
  }
  
  .chunky-sales {
    font-size: 1rem;
  }
}
</style>
