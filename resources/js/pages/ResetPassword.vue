<template>
  <div class="reset-container">
    <div class="reset-card">
      <h1>Ganti Password</h1>
      <p class="subtitle">Demi keamanan, kamu wajib mengganti password default terlebih dahulu.</p>

      <form @submit.prevent="handleReset">
        <div class="form-group">
          <label>Password Baru</label>
          <input v-model="newPassword" type="password" required minlength="6" />
        </div>

        <div class="form-group">
          <label>Konfirmasi Password Baru</label>
          <input v-model="confirmPassword" type="password" required minlength="6" />
        </div>

        <p v-if="errorMessage" class="error">{{ errorMessage }}</p>

        <button type="submit" :disabled="isSaving">
          {{ isSaving ? 'Menyimpan...' : 'Ganti Password' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
    import { ref } from 'vue'
    import { useRouter } from 'vue-router'
    import api from '../api'
    import { authStore } from '../store/auth'

    const router = useRouter()
    const newPassword = ref('')
    const confirmPassword = ref('')
    const errorMessage = ref('')
    const isSaving = ref(false)

    async function handleReset() {
    errorMessage.value = ''

    if (newPassword.value !== confirmPassword.value) {
        errorMessage.value = 'Konfirmasi password tidak cocok.'
        return
    }

    isSaving.value = true
    try {
        await api.post('/reset-password', {
        new_password: newPassword.value,
        new_password_confirmation: confirmPassword.value,
        })

        authStore.state.user.is_change_default_password = false
        localStorage.setItem('user', JSON.stringify(authStore.state.user))

        router.push({ name: 'dashboard' })
    } catch (error) {
        errorMessage.value = error.response?.data?.message || 'Gagal mengganti password.'
    } finally {
        isSaving.value = false
    }
    }
</script>

<style scoped>
    .reset-container {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    background: #f3f4f6;
    }
    .reset-card {
    background: white;
    padding: 2.5rem;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 400px;
    }
    h1 {
    margin: 0 0 0.5rem;
    font-size: 1.25rem;
    color: #1f2937;
    text-align: center;
    }
    .subtitle {
    margin: 0 0 1.5rem;
    color: #6b7280;
    font-size: 0.85rem;
    text-align: center;
    }
    .form-group {
    margin-bottom: 1rem;
    }
    label {
    display: block;
    margin-bottom: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    }
    input {
    width: 100%;
    padding: 0.625rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.9rem;
    box-sizing: border-box;
    }
    .error {
    color: #dc2626;
    font-size: 0.85rem;
    margin: 0.5rem 0;
    }
    button {
    width: 100%;
    padding: 0.7rem;
    background: #4f46e5;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 0.95rem;
    font-weight: 500;
    cursor: pointer;
    margin-top: 0.5rem;
    }
    button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    }
</style>