<template>
    <div class="container">
      <qrcode-stream @detect="onDetect"></qrcode-stream>
      <p v-if="qrCode">📌{{ t('qrScanner.title') }}: {{ qrCode }}</p>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { QrcodeStream } from 'vue-qrcode-reader';
import { useRouter } from 'vue-router';
import { useI18n } from '#imports'
const { t } = useI18n()

const qrCode = ref('');
const router = useRouter();

const onDetect = (detectedCodes) => {
    qrCode.value = detectedCodes[0]?.rawValue || t('qrScanner.error');
    if (qrCode.value !== t('qrScanner.error')) {
        if (qrCode.value.startsWith('http://') || qrCode.value.startsWith('https://')) {
            window.location.href = qrCode.value;
        } else {
            router.push(qrCode.value);
            window.location.reload();
        }
    }
};
</script>
  