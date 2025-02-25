<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from '#imports'
const { t } = useI18n()

const router = useRouter()
const inputCode = ref('')

const goToTable = () => {
    if (inputCode.value.trim() === '') {
        alert('Please enter a code.')
        return
    }
    router.push(`/table/${inputCode.value}`)
}
const goToScanner = () => {
    router.push('/QRscannerPage')
    //window.location.reload()
}
</script>
<template>



    <section class="hero d-flex align-items-center">
        <div class="container">
            <div class="hero-content text-center">
                <h1 class="display-4 fw-bold mb-4 text-white heading">{{ t('qrReaded.title') }}</h1>
                <p class="lead mb-4 text-white">{{ t('qrReaded.pharagraph') }}</p>

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control " id="floatingInput" placeholder="" v-model="inputCode">
                        <label for="floatingInput">{{ t('qrReaded.code') }}</label>
                    </div>
                    <ButtonComponet :text="t('qrReaded.search')" @click="goToTable" />
                    <ButtonComponet :text="t('qrReaded.scanqr')" @click="goToScanner"/>
            </div>
        </div>
    </section>

</template>
<style scoped>
@import url("~/assets/css/main.css");

/* Hero Section Styles */
.hero {
    background: linear-gradient(to bottom, transparent, black);
    height: 100vh;
    /* Full viewport height */
}

.hero-content {
    max-width: 800px;
    margin: 0 auto;
}

.hero h1 {
    font-size: 3.5rem;
    font-weight: bold;
    margin-bottom: 1.5rem;
}

.hero p {
    font-size: 1.25rem;
    margin-bottom: 2rem;
}

</style>
rewrite every bootsrap and css to tailwind