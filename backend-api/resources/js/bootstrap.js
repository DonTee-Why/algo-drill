import axios from 'axios';
import { Ziggy } from './ziggy';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;
window.axios.defaults.withXSRFToken = true;

// Use the current host so named routes aren't stuck on APP_URL (localhost).
Ziggy.url = window.location.origin;
// Ziggy.port = null;

window.Ziggy = Ziggy;
