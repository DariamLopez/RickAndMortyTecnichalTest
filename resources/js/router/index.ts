import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const routes = [
    {
        path: '/',
        name: 'characters.index',
        component: () => import('@/views/CharacterListView.vue'),
    },
    {
        path: '/characters/:id',
        name: 'characters.show',
        component: () => import('@/views/CharacterDetailView.vue'),
        props: true,
    },
    {
        path: '/favorites',
        name: 'favorites.index',
        component: () => import('@/views/FavoritesView.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/login',
        name: 'login',
        component: () => import('@/views/LoginView.vue'),
        meta: { guestOnly: true },
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('@/views/RegisterView.vue'),
        meta: { guestOnly: true },
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: { name: 'characters.index' },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to) => {
    const authStore = useAuthStore();

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        return { name: 'login', query: { redirect: to.fullPath } };
    }

    if (to.meta.guestOnly && authStore.isAuthenticated) {
        return { name: 'characters.index' };
    }

    return true;
});

export default router;
