import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';


export default defineNuxtPlugin(() => {

  console.log('loading echo.client.js');
  window.axios = axios;
  window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';


  window.Pusher = Pusher;
  
  const ws =new Echo({
    broadcaster: 'reverb',
    key: 'oevgiouibc6ohhmnpuab',
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
