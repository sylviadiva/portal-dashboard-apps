<template>
  <div>
    <div class="dashboard-content">
      <h1>Dashboard</h1>
      <p class="welcome-text">Halo, {{ authStore.state.user?.name }}! Ini aplikasi yang bisa kamu akses :</p>
    </div>

    <main class="dashboard-main">
      <p v-if="isLoading" class="status-text">Memuat aplikasi...</p>
      <p v-else-if="applications.length === 0" class="status-text">
        Kamu belum punya akses ke aplikasi apapun. Silakan hubungi Admin.
      </p>

      <div v-else class="app-grid">
        <a
          v-for="app in applications"
          :key="app.id"
          :href="app.url"
          target="_blank"
          rel="noopener noreferrer"
          class="app-card"
          :style="{ borderTopColor: app.color || '#4f46e5' }"
        >
          <div class="app-icon" :style="{ background: app.color || '#4f46e5' }">
            {{ app.name.charAt(0) }}
          </div>
          <div class="app-name">{{ app.name }}</div>
          <div class="app-category">{{ app.category }}</div>
        </a>
      </div>
    </main>
  </div>
</template>

<script setup>
  import { ref, onMounted } from 'vue'
  import api from '../api'
  import { authStore } from '../store/auth'

  const applications = ref([])
  const isLoading = ref(true)

  onMounted(async () => {
    try {
      const response = await api.get('/dashboard/my-applications')
      applications.value = response.data
    } catch (error) {
      console.error('Gagal memuat aplikasi:', error)
    } finally {
      isLoading.value = false
    }
  })
</script>

<style scoped>
  .dashboard-content {
    background: white;
    padding: 1.5rem 2rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
  }
  .dashboard-content h1 {
    margin: 0;
    font-size: 1.25rem;
    color: #1f2937;
  }
  .welcome-text {
    margin: 0.375rem 0 0;
    font-size: 0.875rem;
    color: #6b7280;
  }
  .dashboard-main {
    padding: 2rem;
    max-width: 1100px;
    margin: 0 auto;
  }
  .status-text {
    color: #6b7280;
    text-align: center;
    margin-top: 3rem;
  }
  .app-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 1.25rem;
  }
  .app-card {
    background: white;
    border-radius: 12px;
    border-top: 4px solid;
    padding: 1.5rem 1rem;
    text-align: center;
    text-decoration: none;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    transition: transform 0.15s, box-shadow 0.15s;
  }
  .app-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }
  .app-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0 auto 0.75rem;
  }
  .app-name {
    font-size: 0.9rem;
    font-weight: 600;
    color: #1f2937;
  }
  .app-category {
    font-size: 0.75rem;
    color: #9ca3af;
    margin-top: 0.25rem;
  }
</style>