<template>
  <div class="admin-container">
    <header class="admin-header">
      <h1>Kelola Aplikasi</h1>
    </header>

    <main class="admin-main">
      <div class="toolbar">
        <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari nama, URL, atau kategori..."
            class="search-input"
        />
        <button @click="openCreateForm" class="btn-primary">+ Tambah Aplikasi</button>
      </div>

      <table class="app-table">
        <thead>
          <tr>
            <th @click="toggleSort('name')" class="sortable">
              Nama {{ sortKey === 'name' ? (sortOrder === 'asc' ? '▲' : '▼') : '' }}
            </th>
            <th>URL</th>
            <th @click="toggleSort('category')" class="sortable">
              Kategori {{ sortKey === 'category' ? (sortOrder === 'asc' ? '▲' : '▼') : '' }}
            </th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="app in paginatedApplications" :key="app.id">
            <td>{{ app.name }}</td>
            <td><a :href="app.url" target="_blank">{{ app.url }}</a></td>
            <td>{{ app.category || '-' }}</td>
            <td class="actions">
              <button @click="openEditForm(app)" class="btn-link">Edit</button>
              <button @click="confirmDelete(app)" class="btn-link btn-danger">Hapus</button>
            </td>
          </tr>
          <tr v-if="filteredApplications.length === 0">
            <td colspan="4" class="empty-text">Tidak ada aplikasi yang cocok.</td>
          </tr>
        </tbody>
      </table>

      <div class="pagination-wrapper">
        <p class="pagination-info">
          Menampilkan {{ startItem }}-{{ endItem }} dari {{ filteredApplications.length }} aplikasi
        </p>
        <div v-if="totalPages > 1" class="pagination">
          <button
            v-for="page in totalPages"
            :key="page"
            @click="goToPage(page)"
            :class="{ active: currentPage === page }"
          >
            {{ page }}
          </button>
        </div>
      </div>
    </main>

    <!-- Modal Form Tambah/Edit -->
    <div v-if="showForm" class="modal-overlay" @click.self="closeForm">
      <div class="modal-card">
        <h2>{{ isEditing ? 'Edit Aplikasi' : 'Tambah Aplikasi' }}</h2>

        <form @submit.prevent="handleSubmit">
          <div class="form-group">
            <label>Nama Aplikasi</label>
            <input v-model="form.name" type="text" required />
          </div>

          <div class="form-group">
            <label>URL</label>
            <input v-model="form.url" type="url" required placeholder="https://" />
          </div>

          <div class="form-group">
            <label>Kategori</label>
            <input v-model="form.category" type="text" placeholder="misal: Finance, IT, Marketing" />
          </div>

          <div class="form-group">
            <label>Warna (untuk tampilan card)</label>
            <input v-model="form.color" type="color" />
          </div>

          <p v-if="errorMessage" class="error">{{ errorMessage }}</p>

          <div class="modal-actions">
            <button type="button" @click="closeForm" class="btn-secondary">Batal</button>
            <button type="submit" :disabled="isSaving" class="btn-primary">
              {{ isSaving ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { ref, onMounted, reactive, computed, watch } from 'vue'
  import api from '../../api'

  const applications = ref([])
  const showForm = ref(false)
  const isEditing = ref(false)
  const isSaving = ref(false)
  const errorMessage = ref('')
  const editingId = ref(null)

  const form = reactive({
    name: '',
    url: '',
    category: '',
    color: '#4f46e5',
  })

  async function fetchApplications() {
    const response = await api.get('/applications')
    applications.value = response.data
  }

  onMounted(fetchApplications)

  // --- Search ---
  const searchQuery = ref('')

  function matchesSearch(app, query) {
    const searchableFields = [app.name, app.url, app.category]
    return searchableFields.some((field) =>
      (field || '').toLowerCase().includes(query)
    )
  }

  const filteredApplications = computed(() => {
    if (!searchQuery.value.trim()) return applications.value

    const query = searchQuery.value.toLowerCase()
    return applications.value.filter((app) => matchesSearch(app, query))
  })

  // --- Sorting ---
  const sortKey = ref('name')
  const sortOrder = ref('asc')

  function toggleSort(key) {
    if (sortKey.value === key) {
      sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
    } else {
      sortKey.value = key
      sortOrder.value = 'asc'
    }
    currentPage.value = 1
  }

  const sortedApplications = computed(() => {
    return [...filteredApplications.value].sort((a, b) => {
      const valA = (a[sortKey.value] || '').toLowerCase()
      const valB = (b[sortKey.value] || '').toLowerCase()
      if (valA < valB) return sortOrder.value === 'asc' ? -1 : 1
      if (valA > valB) return sortOrder.value === 'asc' ? 1 : -1
      return 0
    })
  })

  // --- Pagination ---
  const currentPage = ref(1)
  const perPage = 5

  const totalPages = computed(() => Math.ceil(sortedApplications.value.length / perPage))

  const paginatedApplications = computed(() => {
    const start = (currentPage.value - 1) * perPage
    return sortedApplications.value.slice(start, start + perPage)
  })

  const startItem = computed(() => {
    if (filteredApplications.value.length === 0) return 0
    return (currentPage.value - 1) * perPage + 1
  })

  const endItem = computed(() => {
    return Math.min(currentPage.value * perPage, filteredApplications.value.length)
  })

  function goToPage(page) {
    currentPage.value = page
  }

  // --- Form Create/Edit ---
  function resetForm() {
    form.name = ''
    form.url = ''
    form.category = ''
    form.color = '#4f46e5'
    errorMessage.value = ''
    editingId.value = null
  }

  function openCreateForm() {
    resetForm()
    isEditing.value = false
    showForm.value = true
  }

  function openEditForm(app) {
    form.name = app.name
    form.url = app.url
    form.category = app.category || ''
    form.color = app.color || '#4f46e5'
    editingId.value = app.id
    isEditing.value = true
    showForm.value = true
  }

  function closeForm() {
    showForm.value = false
  }

  async function handleSubmit() {
    errorMessage.value = ''
    isSaving.value = true

    try {
      if (isEditing.value) {
        await api.put(`/applications/${editingId.value}`, form)
      } else {
        await api.post('/applications', form)
      }
      await fetchApplications()
      closeForm()
    } catch (error) {
      const errors = error.response?.data?.errors
      errorMessage.value = errors
        ? Object.values(errors).flat().join(', ')
        : 'Gagal menyimpan aplikasi.'
    } finally {
      isSaving.value = false
    }
  }

  async function confirmDelete(app) {
    if (!confirm(`Hapus aplikasi "${app.name}"? Rule akses yang terkait juga akan ikut terhapus.`)) {
      return
    }

    try {
      await api.delete(`/applications/${app.id}`)
      await fetchApplications()
    } catch (error) {
      alert('Gagal menghapus aplikasi.')
    }
  }

  watch(searchQuery, () => {
    currentPage.value = 1
  })
</script>

<style scoped>
  .admin-container {
    min-height: 100vh;
    background: #f9fafb;
  }
  .admin-header {
    background: white;
    padding: 1.25rem 2rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
  }
  .admin-header h1 {
    margin: 0;
    font-size: 1.25rem;
    color: #1f2937;
  }
  .admin-main {
    padding: 2rem;
    max-width: 900px;
    margin: 0 auto;
  }
  .toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    gap: 1rem;
  }
  .search-input {
    flex: 1;
    max-width: 320px;
    padding: 0.5rem 0.85rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.85rem;
    box-sizing: border-box;
  }
  .search-input:focus {
    outline: none;
    border-color: #4f46e5;
  }
  .total-text {
    margin: 0;
    font-size: 0.85rem;
    color: #6b7280;
  }
  .app-table {
    width: 100%;
    background: white;
    border-radius: 12px;
    border-collapse: collapse;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
  }
  .app-table th, .app-table td {
    text-align: left;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f3f4f6;
    font-size: 0.875rem;
  }
  .app-table th {
    background: #f9fafb;
    color: #6b7280;
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
  }
  .sortable {
    cursor: pointer;
    user-select: none;
  }
  .sortable:hover {
    color: #4f46e5;
  }
  .app-table a {
    color: #4f46e5;
    text-decoration: none;
  }
  .actions {
    display: flex;
    gap: 0.75rem;
  }
  .empty-text {
    text-align: center;
    color: #9ca3af;
    padding: 2rem;
  }
  .pagination-wrapper {
    margin-top: 1rem;
    text-align: center;
  }
  .pagination-info {
    font-size: 0.8rem;
    color: #9ca3af;
    margin: 0 0 0.5rem;
  }
  .pagination {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
  }
  .pagination button {
    width: 32px;
    height: 32px;
    border: 1px solid #e5e7eb;
    background: white;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.85rem;
  }
  .pagination button.active {
    background: #4f46e5;
    color: white;
    border-color: #4f46e5;
  }
  .btn-primary {
    padding: 0.6rem 1.25rem;
    background: #4f46e5;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
  }
  .btn-primary:hover:not(:disabled) {
    background: #4338ca;
  }
  .btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }
  .btn-secondary {
    padding: 0.6rem 1.25rem;
    background: #f3f4f6;
    color: #374151;
    border: none;
    border-radius: 8px;
    font-size: 0.875rem;
    cursor: pointer;
  }
  .btn-link {
    background: none;
    border: none;
    color: #4f46e5;
    font-size: 0.85rem;
    cursor: pointer;
    padding: 0;
  }
  .btn-danger {
    color: #dc2626;
  }
  .modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 50;
  }
  .modal-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    width: 100%;
    max-width: 420px;
  }
  .modal-card h2 {
    margin: 0 0 1.25rem;
    font-size: 1.1rem;
    color: #1f2937;
  }
  .form-group {
    margin-bottom: 1rem;
  }
  .form-group label {
    display: block;
    margin-bottom: 0.375rem;
    font-size: 0.85rem;
    font-weight: 500;
    color: #374151;
  }
  .form-group input[type="text"],
  .form-group input[type="url"] {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.875rem;
    box-sizing: border-box;
  }
  .error {
    color: #dc2626;
    font-size: 0.8rem;
  }
  .modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    margin-top: 1.25rem;
  }
</style>