<template>
  <div>
    <div class="page-header">
      <h1>Kelola User</h1>
    </div>

    <main class="page-main">
      <div class="toolbar">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari nama, email, departemen, atau role ..."
          class="search-input"
        />
        <button @click="openCreateForm" class="btn-primary">+ Tambah User</button>
      </div>

      <table class="user-table">
        <thead>
          <tr>
            <th @click="toggleSort('name')" class="sortable">
              Nama {{ sortKey === 'name' ? (sortOrder === 'asc' ? '▲' : '▼') : '' }}
            </th>
            <th>Email</th>
            <th>Departemen</th>
            <th>Role</th>
            <th>Admin?</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in paginatedUsers" :key="user.id">
            <td>{{ user.name }}</td>
            <td>{{ user.email }}</td>
            <td>{{ user.department?.name || '-' }}</td>
            <td>{{ user.role?.name || '-' }}</td>
            <td>
              <span v-if="user.is_admin" class="badge-admin">Admin</span>
              <span v-else>-</span>
            </td>
            <td class="actions">
              <button @click="openEditForm(user)" class="btn-link">Edit</button>
              <button @click="confirmDelete(user)" class="btn-link btn-danger">Hapus</button>
            </td>
          </tr>
          <tr v-if="filteredUsers.length === 0">
            <td colspan="6" class="empty-text">Belum ada user.</td>
          </tr>
        </tbody>
      </table>

      <div class="pagination-wrapper">
        <p class="pagination-info">
          Menampilkan {{ startItem }}-{{ endItem }} dari {{ filteredUsers.length }} user
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

    <div v-if="showForm" class="modal-overlay" @click.self="closeForm">
      <div class="modal-card">
        <h2>{{ isEditing ? 'Edit User' : 'Tambah User' }}</h2>

        <form @submit.prevent="handleSubmit">
          <div class="form-group">
            <label>Nama</label>
            <input v-model="form.name" type="text" required />
          </div>

          <div class="form-group">
            <label>Email</label>
            <input v-model="form.email" type="email" required />
          </div>

          <div v-if="!isEditing" class="form-group info-box">
            ℹ️ Password default: <strong>password</strong>. User wajib menggantinya saat login pertama.
          </div>

          <div v-if="isEditing" class="form-group checkbox-group">
            <label>
              <input type="checkbox" v-model="form.reset_password" />
              Reset password ke default ("password")
            </label>
          </div>

          <div class="form-group">
            <label>Departemen</label>
            <select v-model="form.department_id">
              <option :value="null">-- Tanpa Departemen --</option>
              <option v-for="dept in references.departments" :key="dept.id" :value="dept.id">
                {{ dept.name }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label>Role</label>
            <select v-model="form.role_id">
              <option :value="null">-- Tanpa Role --</option>
              <option v-for="role in references.roles" :key="role.id" :value="role.id">
                {{ role.name }}
              </option>
            </select>
          </div>

          <div class="form-group checkbox-group">
            <label>
              <input type="checkbox" v-model="form.is_admin" />
              Jadikan admin
            </label>
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

  const users = ref([])
  const references = ref({ departments: [], roles: [] })
  const showForm = ref(false)
  const isEditing = ref(false)
  const isSaving = ref(false)
  const errorMessage = ref('')
  const editingId = ref(null)

  const form = reactive({
    name: '',
    email: '',
    department_id: null,
    role_id: null,
    is_admin: false,
    reset_password: false,
  })

  async function fetchUsers() {
    const response = await api.get('/users')
    users.value = response.data
  }

  async function fetchReferences() {
    const response = await api.get('/references')
    references.value = response.data
  }

  onMounted(async () => {
    await Promise.all([fetchUsers(), fetchReferences()])
  })

  // --- Search ---
  const searchQuery = ref('')

  function matchesSearch(user, query) {
    const searchableFields = [user.name, user.email, user.role?.name, user.department?.name]
    return searchableFields.some((field) =>
      (field || '').toLowerCase().includes(query)
    )
  }

  const filteredUsers = computed(() => {
    if (!searchQuery.value.trim()) return users.value

    const query = searchQuery.value.toLowerCase()
    return users.value.filter((user) => matchesSearch(user, query))
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

  const sortedUsers = computed(() => {
    return [...filteredUsers.value].sort((a, b) => {
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

  const totalPages = computed(() => Math.ceil(sortedUsers.value.length / perPage))

  const paginatedUsers = computed(() => {
    const start = (currentPage.value - 1) * perPage
    return sortedUsers.value.slice(start, start + perPage)
  })

  const startItem = computed(() => {
    if (filteredUsers.value.length === 0) return 0
    return (currentPage.value - 1) * perPage + 1
  })

  const endItem = computed(() => {
    return Math.min(currentPage.value * perPage, filteredUsers.value.length)
  })

  function goToPage(page) {
    currentPage.value = page
  }

  // --- Form Create/Edit ---
  function resetForm() {
    form.name = ''
    form.email = ''
    form.department_id = null
    form.role_id = null
    form.is_admin = false
    form.reset_password = false
    errorMessage.value = ''
    editingId.value = null
  }

  function openCreateForm() {
    resetForm()
    isEditing.value = false
    showForm.value = true
  }

  function openEditForm(user) {
    form.name = user.name
    form.email = user.email
    form.department_id = user.department_id
    form.role_id = user.role_id
    form.is_admin = user.is_admin
    form.reset_password = false
    editingId.value = user.id
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
        await api.put(`/users/${editingId.value}`, form)
      } else {
        await api.post('/users', form)
      }
      await fetchUsers()
      closeForm()
    } catch (error) {
      const errors = error.response?.data?.errors
      errorMessage.value = errors
        ? Object.values(errors).flat().join(', ')
        : 'Gagal menyimpan user.'
    } finally {
      isSaving.value = false
    }
  }

  async function confirmDelete(user) {
    if (!confirm(`Hapus user "${user.name}"?`)) return

    try {
      await api.delete(`/users/${user.id}`)
      await fetchUsers()
    } catch (error) {
      alert('Gagal menghapus user.')
    }
  }

  watch(searchQuery, () => {
    currentPage.value = 1
  })
</script>

<style scoped>
.page-header {
  background: white;
  padding: 1.5rem 2rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}
.page-header h1 {
  margin: 0;
  font-size: 1.25rem;
  color: #1f2937;
}
.page-main {
  padding: 2rem;
  max-width: 950px;
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
.user-table {
  width: 100%;
  background: white;
  border-radius: 12px;
  border-collapse: collapse;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}
.user-table th, .user-table td {
  text-align: left;
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #f3f4f6;
  font-size: 0.875rem;
}
.user-table th {
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
.badge-admin {
  background: #eef2ff;
  color: #4f46e5;
  font-size: 0.7rem;
  font-weight: 600;
  padding: 0.15rem 0.5rem;
  border-radius: 999px;
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
  max-height: 90vh;
  overflow-y: auto;
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
.form-group input[type="email"],
.form-group select {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.875rem;
  box-sizing: border-box;
}
.info-box {
  background: #eff6ff;
  color: #1e40af;
  padding: 0.6rem 0.75rem;
  border-radius: 8px;
  font-size: 0.8rem;
}
.checkbox-group label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 400;
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