import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';


export default defineNuxtPlugin(() => {
  if (import.meta.server) return;
  
  console.log('loading echo.client.js');
  window.axios = axios;
  window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';


  window.Pusher = Pusher;
  
  const config = useRuntimeConfig();

  console.log('REVERB_KEY:', config.public.reverbKey);

  const ws =new Echo({
    broadcaster: 'reverb',
    key: config.public.reverbKey,
    wsHost: 'localhost',
    wsPort: 8080,
    wssPort: 443,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
  });

  window.Echo = ws;

  return {
    provide: {
      ws
    }
  }
});
