import axios from 'axios';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Enable credentials (cookies) for same-origin requests
window.axios.defaults.withCredentials = true;

// Get CSRF token from meta tag or cookie
const token = document.head.querySelector<HTMLMetaElement>('meta[name="csrf-token"]');
if (token) {
  window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}
