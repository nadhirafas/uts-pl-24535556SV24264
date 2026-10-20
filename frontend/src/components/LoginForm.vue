
<script setup>
import { ref } from 'vue'
import api from '../api'

const emit = defineEmits(['success'])
const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

async function submit() {
  error.value = ''
  loading.value = true

  try {
    const { data } = await api.post('/login', {
      email: email.value,
      password: password.value,
    })

    localStorage.setItem('token', data.token)
    emit('success')
  } catch (e) {
    error.value =
      e.response?.data?.message ?? 'Gagal terhubung ke server.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="login-layout">
    <section class="login-visual">
      <div>
        <div class="brand-mark">✳</div>
        <div class="eyebrow" style="color: #C4A4DA; margin-top: 30px">
          A LITTLE SPACE FOR BOOKS
        </div>
        <h1>Every book has a story.</h1>
        <p>
          Simpan dan kelola koleksi buku favoritmu dengan cara yang lebih
          sederhana.
        </p>
      </div>

      <div class="login-note">
        A calmer way to organize your reading space.
      </div>
    </section>

    <form class="card login-form-card" @submit.prevent="submit">
      <div class="eyebrow">WELCOME BACK</div>
      <h2>Masuk ke Pustaka</h2>
      <p class="page-subtitle">
        Masuk untuk melanjutkan pengelolaan koleksi buku.
      </p>

      <label>
        Email
        <input
          v-model="email"
          type="email"
          placeholder="nama@email.com"
          required
          autocomplete="username"
        />
      </label>

      <label>
        Password
        <input
          v-model="password"
          type="password"
          placeholder="Masukkan password"
          required
          autocomplete="current-password"
        />
      </label>

      <p v-if="error" class="error">{{ error }}</p>

      <button type="submit" :disabled="loading">
        {{ loading ? 'Memproses...' : 'Masuk ke akun →' }}
      </button>

      <p class="muted" style="margin: 22px 0 0; font-size: 0.78rem; text-align: center">
        Personal book management
      </p>
    </form>
  </div>
</template>
