<script setup lang="ts">
const props = defineProps<{
  allergens: string[]
}>()

const allergenIcons: Record<string, string> = {
  gluten: '🌾',
  dairy: '🥛',
  nuts: '🥜',
  eggs: '🥚',
  soy: '🫘',
  shellfish: '🦐'
}
</script>

<template>
  <div class="allergen-info">
    <h4>Allergen Information</h4>
    <div class="allergen-list">
      <div v-for="allergen in allergens" :key="allergen" class="allergen-item">
        <span class="allergen-icon">{{ allergenIcons[allergen] || '⚠️' }}</span>
        <span class="allergen-name">{{ allergen }}</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
.allergen-info {
  margin-top: 1rem;
  padding: 1rem;
  background: #fff4f4;
  border-radius: 8px;
  border: 2px solid #ffcdd2;
}

.allergen-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.allergen-item {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.3rem 0.6rem;
  background: white;
  border-radius: 15px;
  border: 1px solid #ffcdd2;
}

.allergen-icon {
  font-size: 1.2rem;
}

.allergen-name {
  font-size: 0.9rem;
  color: #333;
}
</style>
