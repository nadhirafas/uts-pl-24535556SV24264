
<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '../api'

const emit = defineEmits(['logout'])
const books = ref([])
const errors = ref({})
const message = ref('')
const search = ref('')
const loading = ref(false)

const form = reactive({
  id: null,
  title: '',
  author: '',
  year: '',
})

const filteredBooks = computed(() => {
  const keyword = search.value.trim().toLowerCase()

  if (!keyword) return books.value

  return books.value.filter((book) =>
    [book.title, book.author, book.year]
      .some((value) => String(value ?? '').toLowerCase().includes(keyword))
  )
})

const latestYear = computed(() => {
  const years = books.value
    .map((book) => Number(book.year))
    .filter((year) => Number.isInteger(year) && year > 0)

  return years.length ? Math.max(...years) : '—'
})

async function load() {
  loading.value = true
  message.value = ''

  try {
    const { data } = await api.get('/books')
    books.value = data.data
  } catch {
    message.value = 'Gagal memuat data buku. Coba muat ulang halaman.'
  } finally {
    loading.value = false
  }
}

function reset() {
  Object.assign(form, {
    id: null,
    title: '',
    author: '',
    year: '',
  })

  errors.value = {}
  message.value = ''
}

async function save() {
  errors.value = {}
  message.value = ''

  const payload = {
    title: form.title,
    author: form.author,
    year: form.year === '' ? null : Number(form.year),
  }

  try {
    if (form.id) {
      await api.put(`/books/${form.id}`, payload)
    } else {
      await api.post('/books', payload)
    }

    reset()
    await load()
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors
    } else {
      message.value = 'Terjadi kesalahan pada server.'
    }
  }
}

function edit(book) {
  Object.assign(form, {
    id: book.id,
    title: book.title,
    author: book.author,
    year: book.year ?? '',
  })

  errors.value = {}
  message.value = ''

  window.scrollTo({ top: 0, behavior: 'smooth' })
}

async function remove(book) {
  if (!confirm(`Hapus "${book.title}" dari koleksi?`)) return

  message.value = ''

  try {
    await api.delete(`/books/${book.id}`)
    await load()
  } catch {
    message.value = 'Buku gagal dihapus. Silakan coba lagi.'
  }
}

async function logout() {
  try {
    await api.post('/logout')
  } finally {
    localStorage.removeItem('token')
    emit('logout')
  }
}

onMounted(load)
</script>

<template>
  <div class="bar" style="margin-bottom: 22px">
    <div>
      <div class="eyebrow">YOUR COLLECTION</div>
      <h2 style="margin: 5px 0 0">Ruang baca</h2>
    </div>

    <button type="button" @click="logout">
      Keluar ↗
    </button>
  </div>

  <section class="stat-grid">
    <div class="stat-card">
      <div class="stat-icon">▤</div>
      <div>
        <div class="stat-label">Total koleksi</div>
        <p class="stat-number">{{ books.length }}</p>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-icon">✳</div>
      <div>
        <div class="stat-label">Tahun terbaru</div>
        <p class="stat-number">{{ latestYear }}</p>
      </div>
    </div>
  </section>

  <form class="card" @submit.prevent="save">
    <div class="section-heading">
      <div>
        <div class="eyebrow">{{ form.id ? 'UPDATE COLLECTION' : 'ADD TO COLLECTION' }}</div>
        <h2 style="margin-top: 5px; margin-bottom: 5px">
          {{ form.id ? 'Ubah detail buku' : 'Tambahkan buku baru' }}
        </h2>
        <p>Lengkapi informasi buku di bawah ini.</p>
      </div>

      <span class="stat-icon">✎</span>
    </div>

    <label>
      Judul buku
      <input
        v-model="form.title"
        placeholder="Contoh: Filosofi Teras"
        required
        maxlength="255"
      />
      <span v-if="errors.title" class="error">{{ errors.title[0] }}</span>
    </label>

    <label>
      Nama penulis
      <input
        v-model="form.author"
        placeholder="Contoh: Henry Manampiring"
        required
        maxlength="255"
      />
      <span v-if="errors.author" class="error">{{ errors.author[0] }}</span>
    </label>

    <label>
      Tahun terbit
      <input
        v-model="form.year"
        type="number"
        min="1000"
        max="2100"
        placeholder="Contoh: 2018"
      />
      <span v-if="errors.year" class="error">{{ errors.year[0] }}</span>
    </label>

    <p v-if="message" class="error">{{ message }}</p>

    <div class="bar" style="justify-content: flex-start; flex-wrap: wrap">
      <button type="submit" :disabled="loading">
        {{ form.id ? 'Simpan perubahan →' : '+ Tambah buku' }}
      </button>

      <button v-if="form.id" type="button" @click="reset">
        Batal
      </button>
    </div>
  </form>

  <section class="card">
    <div class="section-heading">
      <div>
        <div class="eyebrow">THE BOOKSHELF</div>
        <h2 style="margin-top: 5px">Koleksi buku</h2>
        <p>{{ filteredBooks.length }} buku ditampilkan</p>
      </div>

      <input
        v-model="search"
        class="search-input"
        type="search"
        placeholder="Cari judul atau penulis..."
        aria-label="Cari buku"
      />
    </div>

    <p v-if="message" class="error">{{ message }}</p>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Judul buku</th>
            <th>Penulis</th>
            <th>Tahun</th>
            <th style="text-align: right">Aksi</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="book in filteredBooks" :key="book.id">
            <td>{{ book.title }}</td>
            <td>{{ book.author }}</td>
            <td>
              <span v-if="book.year" class="year-badge">{{ book.year }}</span>
              <span v-else class="muted">—</span>
            </td>
            <td>
              <div class="table-actions">
                <button type="button" @click="edit(book)">Edit</button>
                <button
                  type="button"
                  class="button-danger"
                  @click="remove(book)"
                >
                  Hapus
                </button>
              </div>
            </td>
          </tr>

          <tr v-if="!filteredBooks.length">
            <td colspan="4">
              <div class="empty-state">
                <div class="empty-icon">▤</div>
                <strong>
                  {{ search ? 'Buku tidak ditemukan' : 'Koleksi masih kosong' }}
                </strong>
                <p style="margin: 8px 0 0">
                  {{
                    search
                      ? 'Coba kata kunci lain.'
                      : 'Tambahkan buku pertama melalui formulir di atas.'
                  }}
                </p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</template>
