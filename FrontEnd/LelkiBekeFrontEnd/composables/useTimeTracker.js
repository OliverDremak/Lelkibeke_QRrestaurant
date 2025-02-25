import { ref, onMounted, onBeforeUnmount } from 'vue';

export function useTimeTracker(startTime, status) {
  const elapsedTime = ref(calculateElapsedTime(startTime));
  const cookingTime = ref(0);
  let timer = null;

  function calculateElapsedTime(timestamp) {
    return Math.floor((new Date() - new Date(timestamp)) / 60000);
  }

  function updateTimes() {
    // Always update total elapsed time
    elapsedTime.value = calculateElapsedTime(startTime);
  }

  onMounted(() => {
    updateTimes();
    // Update every 30 seconds to keep time fresh
    timer = setInterval(updateTimes, 30000);
  });

  onBeforeUnmount(() => {
    if (timer) clearInterval(timer);
  });

  return {
    elapsedTime,
    cookingTime
  };
}
