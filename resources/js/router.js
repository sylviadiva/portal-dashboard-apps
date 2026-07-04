import { createRouter, createWebHistory } from 'vue-router';
import { authStore } from './store/auth';

import Login from './pages/Login.vue';
import ResetPassword from './pages/ResetPassword.vue';
import AppLayout from './layouts/AppLayout.vue';
import Dashboard from './pages/Dashboard.vue';
import AdminApplications from './pages/admin/Applications.vue';
import AdminAccess from './pages/admin/AccessSettings.vue';
import Users from './pages/admin/Users.vue';
import EffectiveAccess from './pages/admin/EffectiveAccess.vue';

const routes = [
    { path: '/login', name: 'login', component: Login },
    { path: '/reset-password', name: 'reset-password', component: ResetPassword, meta: { requiresAuth: true } },

    {
        path: '/',
        component: AppLayout,
        meta: { requiresAuth: true },
        children: [
            { path: '', name: 'dashboard', component: Dashboard },
            {
                path: 'admin/applications',
                name: 'admin.applications',
                component: AdminApplications,
                meta: { requiresAdmin: true },
            },
            {
                path: 'admin/access',
                name: 'admin.access',
                component: AdminAccess,
                meta: { requiresAdmin: true },
            },
            {
                path: 'admin/users',
                name: 'admin.users',
                component: Users,
                meta: { requiresAdmin: true },
            },
            {
                path: 'admin/effective-access',
                name: 'admin.effective-access',
                component: EffectiveAccess,
                meta: { requiresAdmin: true },
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to) => {
    if (to.meta.requiresAuth && !authStore.isLoggedIn) {
        return { name: 'login' };
    }

    if (authStore.isLoggedIn && authStore.state.user?.is_change_default_password && to.name !== 'reset-password') {
        return { name: 'reset-password' };
    }

    if (to.meta.requiresAdmin && !authStore.isAdmin) {
        return { name: 'dashboard' };
    }
    if (to.name === 'login' && authStore.isLoggedIn) {
        return { name: 'dashboard' };
    }
});

export default router;