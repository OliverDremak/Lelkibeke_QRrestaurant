<template>
  <div class="language-selector">
    <!-- <select v-model="selectedLanguage" @change="changeLanguage">
        <option v-for="locale in locales" :key="locale.code" :value="locale.code">
          {{ locale.name }}
        </option>
      </select> -->
        <ButtonComponet @click="setLocale('en')" text="Angol"/>
        <ButtonComponet @click="setLocale('hu')" text="Magyar"/>
  </div>
</template>

<script setup>
import { useI18n } from '#imports'

const { setLocale } = useI18n();

</script>

<style scoped>
.language-selector {
  margin: 10px;
  padding: 5px;
}

select {
  font-size: 16px;
  padding: 5px;
}
</style>