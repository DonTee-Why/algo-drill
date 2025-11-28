import axios from 'axios';
import { Ziggy } from './ziggy';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;
window.axios.defaults.withXSRFToken = true;

// Make Ziggy routes available globally
window.Ziggy = Ziggy;
