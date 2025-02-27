<template>
  <div class="dark-mode-toggle">
    <button @click="toggleDarkMode" :class="{ 'dark-mode': isDarkMode, 'light-mode': !isDarkMode }">
      <span v-if="isDarkMode">🌞 {{ t('darkModeToggle.light') }}</span>
      <span v-else>🌙 {{ t('darkModeToggle.dark') }}</span>
    </button>
  </div>
</template>

<style scoped>
button {
  background-color: transparent;
  border: 2px solid currentColor;
  border-radius: 25px;
  font-weight: 600;
  transition: all 0.3s ease;
  cursor: pointer;
  margin: 0 0.5rem;
  font-size: 1rem; /* Match font size with other buttons */
  width: 120px; /* Fixed width to match other buttons */
  height: 38px; /* Fixed height to match other buttons */
  display: flex;
  align-items: center;
  justify-content: center;
}

.nav-button:hover {
  transform: translateY(-2px);
}

button.dark-mode {
  background: white;
  color: #dd6013;
  border: 2px solid #dd6013;
}

button.dark-mode:hover {
  background: #dd6013;
  color: white;
}

button.light-mode {
  background: linear-gradient(45deg, #dd6013, #ffbd00);
  color: white;
  border: none;
}

button.light-mode:hover {
  box-shadow: 0 2px 8px rgba(221, 96, 19, 0.4);
}
</style>
<script>
import { useI18n } from '#imports'

export default {
  setup() {
    // Must call useI18n in setup() first
    const { t } = useI18n()
    return { t }
  },
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

    this.isDarkMode = savedDarkMode !== null
      ? savedDarkMode === 'true'
      : systemPrefersDark;

    document.documentElement.classList.toggle('dark', this.isDarkMode);
  },
};
</script>