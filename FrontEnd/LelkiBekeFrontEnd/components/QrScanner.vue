<template>
    <div class="container">
      <qrcode-stream @detect="onDetect"></qrcode-stream>
      <p v-if="qrCode">📌 Beolvasott QR-kód: {{ qrCode }}</p>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { QrcodeStream } from 'vue-qrcode-reader';
import { useRouter } from 'vue-router';

const qrCode = ref('');
const router = useRouter();

const onDetect = (detectedCodes) => {
    qrCode.value = detectedCodes[0]?.rawValue || 'Nem sikerült beolvasni';
    if (qrCode.value !== 'Nem sikerült beolvasni') {
        if (qrCode.value.startsWith('http://') || qrCode.value.startsWith('https://')) {
            window.location.href = qrCode.value;
        } else {
            router.push(qrCode.value);
            window.location.reload();
        }
    }
};
</script>
  