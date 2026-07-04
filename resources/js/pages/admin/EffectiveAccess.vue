<template>
  <div>
    <div class="page-header">
      <h1>Database View — Effective Access</h1>
      <p class="subtitle">
        Menampilkan gabungan (UNION) akses tiap user dari 3 sumber: departemen, role, dan specific user.
      </p>
    </div>

    <main class="page-main">
      <div class="toolbar">
        <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari nama, email, departemen, role, atau aplikasi..."
            class="search-input"
        />
        <p class="total-text">Total: {{ filteredUsers.length }} user</p>
      </div>

      <p v-if="isLoading" class="status-text">Memuat data...</p>

      <table v-else class="access-table">
        <thead>
          <tr>
            <th @click="toggleSort('name')" class="sortable">
            User {{ sortKey === 'name' ? (sortOrder === 'asc' ? '▲' : '▼') : '' }}
            </th>
            <th @click="toggleSort('department')" class="sortable">
            Departemen {{ sortKey === 'department' ? (sortOrder === 'asc' ? '▲' : '▼') : '' }}
            </th>
            <th @click="toggleSort('role')" class="sortable">
            Role {{ sortKey === 'role' ? (sortOrder === 'asc' ? '▲' : '▼') : '' }}
            </th>
            <th>Aplikasi yang Bisa Diakses</th>
            <th>Jumlah</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in paginatedUsers" :key="user.id">
            <td>
              <div class="user-name">{{ user.name }}</div>
              <div class="user-email">{{ user.email }}</div>
            </td>
            <td>{{ user.department?.name || '-' }}</td>
            <td>{{ user.role?.name || '-' }}</td>
            <td>
              <span v-if="user.applications.length === 0" class="no-access">Tidak ada akses</span>
              <span v-else class="app-tags">
                <span v-for="app in user.applications" :key="app.id" class="app-tag">
                  {{ app.name }}
                </span>
              </span>
            </td>
            <td class="text-center">
              <span class="count-badge">{{ user.applications.length }}</span>
            </td>
          </tr>
          <tr v-if="filteredUsers.length === 0">
            <td colspan="5" class="empty-text">Tidak ada user yang cocok dengan pencarian.</td>
          </tr>
        </tbody>
      </table>

      <div v-if="!isLoading" class="pagination-wrapper">
        <p class="pagination-info">
          Menampilkan {{ startItem }}-{{ endItem }} dari {{ filteredUsers.length }} user
        </p>
        <div v-if="totalPages > 1" class="pagination">
          <button
            v-for="page in totalPages"
            :key="page"
            @click="currentPage = page"
            :class="{ active: currentPage === page }"
          >
            {{ page }}
          </button>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
    import { ref, onMounted, computed, watch } from 'vue'
    import api from '../../api'

    const users = ref([])
    const accessMatrix = ref({})
    const isLoading = ref(true)

    onMounted(async () => {
        const [usersRes, matrixRes] = await Promise.all([
            api.get('/users'),
            api.get('/dashboard/effective-access'),
        ])
        users.value = usersRes.data
        accessMatrix.value = matrixRes.data
        isLoading.value = false
    })

    // Gabungkan data user dengan hasil effective access matrix
    const usersWithAccess = computed(() => {
        return users.value.map((user) => ({
            ...user,
            applications: accessMatrix.value[user.id] || [],
        }))
    })

    // --- Search ---
    const searchQuery = ref('')

    function matchesSearch(user, query) {
        const searchableFields = [
            user.name,
            user.email,
            user.department?.name,
            user.role?.name,
            ...user.applications.map((app) => app.name),
        ]

        return searchableFields.some((field) =>
            (field || '').toLowerCase().includes(query)
        )
    }

    const filteredUsers = computed(() => {
        if (!searchQuery.value.trim()) return usersWithAccess.value

        const query = searchQuery.value.toLowerCase()
        return usersWithAccess.value.filter((user) => matchesSearch(user, query))
    })

    // --- Sorting (dinamis, bisa sort by field apapun) ---
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

    function getSortValue(user, key) {
        if (key === 'department') return user.department?.name || ''
        if (key === 'name') return user.name || ''
        if (key === 'role') return user.role?.name || ''
        return ''
    }

    const sortedUsers = computed(() => {
        return [...filteredUsers.value].sort((a, b) => {
            const valA = getSortValue(a, sortKey.value).toLowerCase()
            const valB = getSortValue(b, sortKey.value).toLowerCase()
            if (valA < valB) return sortOrder.value === 'asc' ? -1 : 1
            if (valA > valB) return sortOrder.value === 'asc' ? 1 : -1
            return 0
        })
    })

    // --- Pagination ---
    const currentPage = ref(1)
    const perPage = 10

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

    // Reset ke halaman 1 setiap kali search berubah, biar tidak "nyasar"
    // di halaman kosong kalau hasil pencarian lebih sedikit dari halaman saat ini.
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
    .subtitle {
    margin: 0.375rem 0 0;
    font-size: 0.8rem;
    color: #6b7280;
    }
    .page-main {
    padding: 2rem;
    max-width: 1100px;
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
    white-space: nowrap;
    }
    .status-text {
    color: #6b7280;
    text-align: center;
    margin-top: 3rem;
    }
    .access-table {
    width: 100%;
    background: white;
    border-radius: 12px;
    border-collapse: collapse;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }
    .access-table th, .access-table td {
    text-align: left;
    padding: 0.85rem 1rem;
    border-bottom: 1px solid #f3f4f6;
    font-size: 0.85rem;
    vertical-align: top;
    }
    .access-table th {
    background: #f9fafb;
    color: #6b7280;
    font-weight: 600;
    font-size: 0.72rem;
    text-transform: uppercase;
    }
    .sortable {
    cursor: pointer;
    user-select: none;
    }
    .sortable:hover {
    color: #4f46e5;
    }
    .user-name {
    font-weight: 600;
    color: #1f2937;
    }
    .user-email {
    font-size: 0.75rem;
    color: #9ca3af;
    margin-top: 0.15rem;
    }
    .app-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    }
    .app-tag {
    background: #eef2ff;
    color: #4338ca;
    font-size: 0.72rem;
    font-weight: 500;
    padding: 0.2rem 0.55rem;
    border-radius: 999px;
    white-space: nowrap;
    }
    .no-access {
    color: #9ca3af;
    font-size: 0.8rem;
    font-style: italic;
    }
    .empty-text {
    text-align: center;
    color: #9ca3af;
    padding: 2rem;
    }
    .text-center {
    text-align: center;
    }
    .count-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    background: #4f46e5;
    color: white;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
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
</style>