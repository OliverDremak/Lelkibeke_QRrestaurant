import { ref } from 'vue';

// Store timers outside of composable to maintain global state
const globalTimers = new Map();
const intervalIds = new Map();

export function useGlobalTimer() {
  const calculateElapsed = (startTime) => {
    return Math.floor((Date.now() - new Date(startTime).getTime()) / 60000);
  };

  const startTracking = (orderId, startTime) => {
    if (!globalTimers.has(orderId)) {
      const elapsed = ref(calculateElapsed(startTime));
      globalTimers.set(orderId, elapsed);

      // Create interval only if it doesn't exist
      if (!intervalIds.has(orderId)) {
        const intervalId = setInterval(() => {
          const currentElapsed = calculateElapsed(startTime);
          const timer = globalTimers.get(orderId);
          if (timer) {
            timer.value = currentElapsed;
          }
        }, 10000); // Update every 10 seconds
        intervalIds.set(orderId, intervalId);
      }
    }
    return globalTimers.get(orderId);
  };

  const stopTracking = (orderId) => {
    if (intervalIds.has(orderId)) {
      clearInterval(intervalIds.get(orderId));
      intervalIds.delete(orderId);
    }
    globalTimers.delete(orderId);
  };

  const getElapsedTime = (orderId) => {
    return globalTimers.get(orderId)?.value || 0;
  };

  const clearAll = () => {
    intervalIds.forEach(clearInterval);
    intervalIds.clear();
    globalTimers.clear();
  };

  const refreshTimer = (orderId, startTime) => {
    const timer = globalTimers.get(orderId);
    if (timer) {
      timer.value = calculateElapsed(startTime);
    }
  };

  return {
    startTracking,
    stopTracking,
    getElapsedTime,
    clearAll,
    refreshTimer
  };
}
