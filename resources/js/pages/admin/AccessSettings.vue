<template>
  <div class="admin-container">
    <header class="admin-header">
      <h1>Pengaturan Hak Akses</h1>
    </header>

    <main class="admin-main">
      <div class="form-group">
        <label>Pilih Aplikasi</label>
        <select v-model="selectedAppId" @change="loadAccessData">
          <option value="" disabled>-- Pilih aplikasi --</option>
          <option v-for="app in applications" :key="app.id" :value="app.id">
            {{ app.name }}
          </option>
        </select>
      </div>

      <div v-if="selectedAppId && !isLoading" class="access-panels">
        <!-- Panel Departemen -->
        <section class="panel">
          <h2>Departemen</h2>
          <p class="panel-desc">Semua user di departemen ini otomatis dapat akses.</p>
          <div class="checkbox-list">
            <label v-for="dept in references.departments" :key="dept.id" class="checkbox-item">
              <input type="checkbox" :value="dept.id" v-model="selectedDepartments" />
              {{ dept.name }}
            </label>
          </div>
          <button @click="saveDepartments" :disabled="isSaving" class="btn-primary">
            Simpan Departemen
          </button>
        </section>

        <!-- Panel Role -->
        <section class="panel">
          <h2>Role / Jabatan</h2>
          <p class="panel-desc">Semua user dengan role ini otomatis dapat akses.</p>
          <div class="checkbox-list">
            <label v-for="role in references.roles" :key="role.id" class="checkbox-item">
              <input type="checkbox" :value="role.id" v-model="selectedRoles" />
              {{ role.name }}
            </label>
          </div>
          <button @click="saveRoles" :disabled="isSaving" class="btn-primary">
            Simpan Role
          </button>
        </section>

        <!-- Panel User Spesifik -->
        <section class="panel">
          <h2>User Spesifik</h2>
          <p class="panel-desc">Akses langsung ke user tertentu, di luar departemen/role.</p>
          <div class="checkbox-list scrollable">
            <label v-for="user in references.users" :key="user.id" class="checkbox-item">
              <input type="checkbox" :value="user.id" v-model="selectedUsers" />
              {{ user.name }} <span class="email-hint">({{ user.email }})</span>
            </label>
          </div>
          <button @click="saveUsers" :disabled="isSaving" class="btn-primary">
            Simpan User
          </button>
        </section>
      </div>

      <p v-if="selectedAppId && isLoading" class="status-text">Memuat data akses...</p>

      <p v-if="saveMessage" class="save-message">{{ saveMessage }}</p>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../api'

const applications = ref([])
const references = ref({ departments: [], roles: [], users: [] })

const selectedAppId = ref('')
const selectedDepartments = ref([])
const selectedRoles = ref([])
const selectedUsers = ref([])

const isLoading = ref(false)
const isSaving = ref(false)
const saveMessage = ref('')

onMounted(async () => {
  const [appsRes, refsRes] = await Promise.all([
    api.get('/applications'),
    api.get('/references'),
  ])
  applications.value = appsRes.data
  references.value = refsRes.data
})

async function loadAccessData() {
  if (!selectedAppId.value) return

  isLoading.value = true
  saveMessage.value = ''

  try {
    const response = await api.get(`/applications/${selectedAppId.value}/access`)
    selectedDepartments.value = response.data.departments.map((d) => d.id)
    selectedRoles.value = response.data.roles.map((r) => r.id)
    selectedUsers.value = response.data.users.map((u) => u.id)
  } finally {
    isLoading.value = false
  }
}

async function saveDepartments() {
  await saveAccess('departments', 'department_ids', selectedDepartments.value)
}

async function saveRoles() {
  await saveAccess('roles', 'role_ids', selectedRoles.value)
}

async function saveUsers() {
  await saveAccess('users', 'user_ids', selectedUsers.value)
}

async function saveAccess(endpoint, payloadKey, ids) {
  isSaving.value = true
  saveMessage.value = ''

  try {
    await api.put(`/applications/${selectedAppId.value}/access/${endpoint}`, {
      [payloadKey]: ids,
    })
    saveMessage.value = 'Berhasil disimpan.'
    setTimeout(() => { saveMessage.value = '' }, 2500)
  } catch (error) {
    saveMessage.value = 'Gagal menyimpan.'
  } finally {
    isSaving.value = false
  }
}
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
  .back-link {
    font-size: 0.85rem;
    color: #6b7280;
    text-decoration: none;
  }
  .admin-header h1 {
    margin: 0.5rem 0 0;
    font-size: 1.25rem;
    color: #1f2937;
  }
  .admin-main {
    padding: 2rem;
    max-width: 1000px;
    margin: 0 auto;
  }
  .form-group {
    margin-bottom: 1.5rem;
  }
  .form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
  }
  .form-group select {
    width: 100%;
    max-width: 400px;
    padding: 0.6rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.9rem;
    background: white;
  }
  .access-panels {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 1.25rem;
  }
  .panel {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
  }
  .panel h2 {
    margin: 0 0 0.25rem;
    font-size: 1rem;
    color: #1f2937;
  }
  .panel-desc {
    margin: 0 0 1rem;
    font-size: 0.78rem;
    color: #9ca3af;
  }
  .checkbox-list {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    margin-bottom: 1.25rem;
  }
  .checkbox-list.scrollable {
    max-height: 240px;
    overflow-y: auto;
  }
  .checkbox-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #374151;
    cursor: pointer;
  }
  .email-hint {
    color: #9ca3af;
    font-size: 0.75rem;
  }
  .status-text {
    color: #6b7280;
    margin-top: 1rem;
  }
  .save-message {
    margin-top: 1rem;
    color: #059669;
    font-size: 0.875rem;
    font-weight: 500;
  }
  .btn-primary {
    width: 100%;
    padding: 0.55rem;
    background: #4f46e5;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 0.85rem;
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
</style>