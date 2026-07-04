<template>
  <div class="app-layout">
    <aside class="sidebar">
      <div class="sidebar-header">
        <h2>Portal Dashboard</h2>
        <p class="user-name">{{ authStore.state.user?.name }}</p>
      </div>

      <nav class="sidebar-nav">
        <router-link to="/" class="nav-item" exact-active-class="active">
          🏠 Dashboard
        </router-link>

        <template v-if="authStore.isAdmin">
          <div class="nav-divider">Admin</div>
          <router-link to="/admin/applications" class="nav-item" active-class="active">
            📦 Kelola Aplikasi
          </router-link>
          <router-link to="/admin/access" class="nav-item" active-class="active">
            🔑 Kelola Akses
          </router-link>
          <router-link to="/admin/users" class="nav-item" active-class="active">
            👤 Kelola User
          </router-link>
          <router-link to="/admin/effective-access" class="nav-item" active-class="active">
            📊 Effective Access
          </router-link>
        </template>
      </nav>

      <button @click="handleLogout" class="logout-btn">Keluar</button>
    </aside>

    <div class="main-content">
      <router-view />
    </div>
  </div>
</template>

<script setup>
  import { onMounted } from 'vue'
  import { useRouter } from 'vue-router'
  import { authStore } from '../store/auth'

  const router = useRouter()

  onMounted(() => {
    authStore.refreshUser()
  })

  async function handleLogout() {
    await authStore.logout()
    router.push({ name: 'login' })
  }
  </script>

  <style scoped>
  .app-layout {
    display: flex;
    min-height: 100vh;
  }
  .sidebar {
    width: 240px;
    background: #1f2937;
    color: white;
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
  }
  .sidebar-header {
    padding: 1.5rem 1.25rem;
    border-bottom: 1px solid #374151;
  }
  .sidebar-header h2 {
    margin: 0;
    font-size: 1.05rem;
  }
  .user-name {
    margin: 0.375rem 0 0;
    font-size: 0.8rem;
    color: #9ca3af;
  }
  .sidebar-nav {
    flex: 1;
    padding: 1rem 0.75rem;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
  }
  .nav-divider {
    font-size: 0.7rem;
    text-transform: uppercase;
    color: #6b7280;
    padding: 0.75rem 0.75rem 0.25rem;
    letter-spacing: 0.05em;
  }
  .nav-item {
    padding: 0.6rem 0.75rem;
    border-radius: 8px;
    color: #d1d5db;
    text-decoration: none;
    font-size: 0.875rem;
  }
  .nav-item:hover {
    background: #374151;
    color: white;
  }
  .nav-item.active {
    background: #4f46e5;
    color: white;
    font-weight: 500;
  }
  .logout-btn {
    margin: 1rem 0.75rem;
    padding: 0.6rem;
    background: #374151;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 0.85rem;
    cursor: pointer;
  }
  .logout-btn:hover {
    background: #4b5563;
  }
  .main-content {
    flex: 1;
    overflow-y: auto;
    background: #f9fafb;
  }
</style>