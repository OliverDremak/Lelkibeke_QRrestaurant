<template>
    <button
      ref="buttonRef"
      class="glow-button"
      @mousemove="updateGlow"
      @mouseleave="resetGlow"
      @click="handleClick"
    >
      <span class="text">{{ buttonText }}</span>
      <span class="glow" :style="glowStyle"></span>
    </button>
  </template>
  
  <script setup>
  import { ref, reactive } from 'vue';

  import { defineProps, defineEmits } from 'vue';

    const props = defineProps({
      text: {
        type: String,
        default: 'Glowing Button'
      }
    });

    const emit = defineEmits(['click']);

  const buttonRef = ref(null);
  const glowStyle = reactive({});

  const updateGlow = (event) => {
    if (!buttonRef.value) return;
    const rect = buttonRef.value.getBoundingClientRect();
    const x = event.clientX - rect.left;
    const y = event.clientY - rect.top;
    glowStyle.background = `radial-gradient(circle at ${x}px ${y}px, rgba(255, 255, 255, 0.4), transparent 30%)`;
    glowStyle.opacity = 1;
  };
  
  const resetGlow = () => {
    glowStyle.opacity = 0;
  };
  
  const handleClick = (event) => {
    emit('click', event);
  };

  const buttonText = props.text;
  </script>
  
  <style scoped>
  @import url("~/assets/css/main.css");
  
  .glow-button .glow {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    transition: opacity 0.3s ease;
    z-index: 1;
  }
  </style>
  
    