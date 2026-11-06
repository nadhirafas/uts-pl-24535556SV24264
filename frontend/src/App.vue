<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import LoginForm from './components/LoginForm.vue'
import BookManager from './components/BookManager.vue'

const token = ref(localStorage.getItem('token'))

const onExpired = () => {
  token.value = null
}

const onLogin = () => {
  token.value = localStorage.getItem('token')
}

onMounted(() => window.addEventListener('auth-expired', onExpired))
onUnmounted(() => window.removeEventListener('auth-expired', onExpired))
</script>

<template>
  <main>
    <header class="page-heading">
      <div class="eyebrow">YOUR PERSONAL LIBRARY</div>
      <h1>Pustaka<span style="color: #9E7EB5">.</span></h1>
      <p class="page-subtitle">
        Ruang kecil untuk mengelola koleksi bukumu.
      </p>
    </header>

    <BookManager
      v-if="token"
      @logout="token = null"
    />

    <LoginForm
      v-else
      @success="onLogin"
    />
  </main>
</template>