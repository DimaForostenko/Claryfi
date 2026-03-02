import { createApp } from 'vue'
import App from './App.vue'
import axios from 'axios'

// Configure axios base URL
axios.defaults.baseURL = import.meta.env.VITE_API_URL || 'http://localhost'
axios.defaults.headers.common['Content-Type'] = 'application/json'
axios.defaults.headers.common['Accept'] = 'application/json'

// Add axios to global properties
const app = createApp(App)

app.config.globalProperties.$axios = axios

app.mount('#app')