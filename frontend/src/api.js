import axios from 'axios'

// Alamat API TIDAK ditulis di komponen: dibaca dari variabel lingkungan Vite.
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  headers: { Accept: 'application/json' },
})

// Kirim token di setiap permintaan
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

// Token ditolak/kedaluwarsa -> hapus token dan beri tahu App.vue
api.interceptors.response.use(
  (res) => res,
  (err) => {
    const isLogin = err.config?.url?.includes('/login')
    if (err.response?.status === 401 && !isLogin) {
      localStorage.removeItem('token')
      window.dispatchEvent(new Event('auth-expired'))
    }
    return Promise.reject(err)
  }
)

export default api
