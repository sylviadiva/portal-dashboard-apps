import { reactive } from 'vue';
import api from '../api';

const state = reactive({
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('token') || null,
});

export const authStore = {
    state,

    get isLoggedIn() {
        return !!state.token;
    },

    get isAdmin() {
        return state.user?.is_admin === true;
    },

    async login(email, password) {
        const response = await api.post('/login', { email, password });
        state.user = response.data.user;
        state.token = response.data.token;
        localStorage.setItem('user', JSON.stringify(state.user));
        localStorage.setItem('token', state.token);
    },

    async logout() {
        try {
            await api.post('/logout');
        } catch (e) {
            // Tetap lanjut clear state lokal walau request logout gagal
        }
        state.user = null;
        state.token = null;
        localStorage.removeItem('user');
        localStorage.removeItem('token');
    },

    async refreshUser() {
        if (!state.token) return;
        const response = await api.get('/me');
        state.user = response.data;
        localStorage.setItem('user', JSON.stringify(state.user));
    },
};