<template>
  <div class="language-selector">
    <select v-model="selectedLanguage" @change="changeLanguage" class="language-dropdown">
      <option v-for="locale in locales" :key="locale.code" :value="locale.code" class="language-option">
        {{ locale.name }}
      </option>
    </select>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useI18n } from '#imports'

const { t, locale, setLocale } = useI18n()

// Nyelvi opciók: itt csak az Angol és a Magyar nyelv szerepel, de bővíthető!
const locales = [
  { code: 'en', name: t('languageSelector.en') },
  { code: 'hu', name: t('languageSelector.hu') },
  { code: 'de', name: t('languageSelector.de') },
  { code: 'fr', name: t('languageSelector.fr') },
  { code: 'es', name: t('languageSelector.es') }
]

// A selectedLanguage kezdetben az aktuális i18n locale értéke
const selectedLanguage = ref(locale.value)

// Ha az i18n locale értéke kívülről megváltozik, frissítjük a selectedLanguage-t
watch(locale, (newLocale) => {
  selectedLanguage.value = newLocale
})

function changeLanguage() {
  setLocale(selectedLanguage.value)
}
</script>

<style scoped>
.language-selector {
  margin: 10px;
  padding: 5px;
  display: inline-block;
  position: relative;
}

.language-dropdown {
  appearance: none; /* Eltávolítja az alapértelmezett nyíl ikont */
  -webkit-appearance: none;
  -moz-appearance: none;
  background-color: #ffffff;
  border: 2px solid #dd6013; /* Használjuk a #dd6013 színt */
  border-radius: 8px;
  padding: 10px 40px 10px 15px;
  font-size: 16px;
  color: #333333;
  cursor: pointer;
  transition: all 0.3s ease;
  outline: none;
}

.language-dropdown:hover {
  border-color: #dd6013;
}

.language-dropdown:focus {
  border-color: #dd6013;
  box-shadow: 0 0 0 3px rgba(221, 96, 19, 0.25);
}

/* Egyedi dropdown nyíl */
.language-selector::after {
  content: '▼';
  position: absolute;
  top: 50%;
  right: 15px;
  transform: translateY(-50%);
  pointer-events: none;
  color: #dd6013; /* Itt is a #dd6013 szín */
  font-size: 12px;
}

/* Option stílusok */
.language-option {
  padding: 10px;
  background-color: #ffffff;
  color: #333333;
}

.language-option:hover {
  background-color: #dd6013;
  color: #ffffff;
}

.nav-select {
  appearance: none;
  background-color: transparent;
  border: none;
  color: inherit;
  font-family: inherit;
  font-size: inherit;
  padding: 0.5rem 1.5rem 0.5rem 0.5rem;
  cursor: pointer;
  outline: none;
  transition: color 0.3s ease;
}

.nav-select:hover,
.nav-select:focus {
  color: #dd6013;
}

.nav-select option {
  background-color: var(--surface-background, #ffffff);
  color: var(--text-primary, #333333);
  padding: 8px;
}

.nav-select option:hover {
  background-color: #dd6013;
  color: #ffffff;
}

:root.dark .nav-select option {
  background-color: #1a1a1a;
  color: #ffffff;
}
</style>