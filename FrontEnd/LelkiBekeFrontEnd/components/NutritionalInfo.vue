<script setup lang="ts">
const props = defineProps<{
  calories: number
  protein: number
  carbs: number
  fat: number
}>()
</script>

<template>
  <div class="nutrition-info">
    <h4>Nutritional Information</h4>
    <div class="nutrition-grid">
      <div class="nutrition-item">
        <span class="label">Calories</span>
        <span class="value">{{ calories }}kcal</span>
      </div>
      <div class="nutrition-item">
        <span class="label">Protein</span>
        <span class="value">{{ protein }}g</span>
      </div>
      <div class="nutrition-item">
        <span class="label">Carbs</span>
        <span class="value">{{ carbs }}g</span>
      </div>
      <div class="nutrition-item">
        <span class="label">Fat</span>
        <span class="value">{{ fat }}g</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
.nutrition-info {
  background: white;
  padding: 1rem;
  border-radius: 8px;
  border: 2px solid #ddd;
}

.nutrition-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.8rem;
  margin-top: 0.5rem;
}

.nutrition-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0.5rem;
  background: #f8f8f8;
  border-radius: 4px;
}

.label {
  font-size: 0.9rem;
  color: #666;
}

.value {
  font-weight: bold;
  color: #333;
}
</style>
