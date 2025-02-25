<template>
  <div class="dark-mode-toggle">
    <button 
      @click="toggleDarkMode"
      :class="{ 'dark-mode': isDarkMode, 'light-mode': !isDarkMode }"
    >
      <span v-if="isDarkMode">🌞 Light</span>
      <span v-else>🌙 Dark</span>
    </button>
  </div>
</template>

<style scoped>

button {
  background-color: transparent;
  border: 2px solid currentColor;
  border-radius: 25px;
  padding: 10px 20px;
  font-size: 1.2em;
  cursor: pointer;
  transition: background-color 0.3s, color 0.3s;
}

button.dark-mode {
  color: #f39c12;
  background-color: #2c3e50;
}

button.light-mode {
  color: #2c3e50;
  background-color: #f39c12;
}

button:hover {
  opacity: 0.8;
}
</style>
  <script>
  export default {
    data() {
      return {
        isDarkMode: false,
      };
    },
    methods: {
      toggleDarkMode() {
        this.isDarkMode = !this.isDarkMode;
        document.documentElement.classList.toggle('dark', this.isDarkMode);
        localStorage.setItem('darkMode', this.isDarkMode);
      },
    },
    mounted() {
      const savedDarkMode = localStorage.getItem('darkMode');
      const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      
      // Handle null/undefined properly
      this.isDarkMode = savedDarkMode !== null 
        ? savedDarkMode === 'true' 
        : systemPrefersDark;
  
      document.documentElement.classList.toggle('dark', this.isDarkMode);
    },
  };
  </script>