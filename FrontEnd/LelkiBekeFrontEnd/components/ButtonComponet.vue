<template>
  <button class="custom-button" @click="handleClick">
    {{ text }}
    <div class="button-effect"></div>
  </button>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  text: {
    type: String,
    default: 'Button'
  }
});

const emit = defineEmits(['click']);

const handleClick = (event) => {
  emit('click', event);
};
</script>

<style scoped>
@import url("~/assets/css/main.css");

.custom-button {
  position: relative;
  padding: 12px 24px;
  margin: 10px;
  background: linear-gradient(45deg, #dd6013, #ffbd00);
  border: none;
  border-radius: 25px;
  color: white;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  overflow: hidden;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(221, 96, 19, 0.2);
}

.custom-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(221, 96, 19, 0.3);
}

.custom-button:active {
  transform: translateY(0);
}

.button-effect {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(
    45deg,
    rgba(255, 255, 255, 0.1),
    rgba(255, 255, 255, 0.2)
  );
  opacity: 0;
  transition: opacity 0.3s ease;
}

.custom-button:hover .button-effect {
  opacity: 1;
}

@media (max-width: 768px) {
  .custom-button {
    padding: 10px 20px;
    font-size: 0.9rem;
  }
}
</style>

