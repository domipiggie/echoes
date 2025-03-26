import { createRouter, createWebHistory } from 'vue-router';
import AuthContainer from '../components/AuthContainer.vue';
import Chat from '../components/Chat.vue';
import { userdataStore } from '../store/UserdataStore';

const routes = [
    {
        path: '/',
        name: 'Login',
        component: AuthContainer
    },
    {
        path: '/chat',
        name: 'Chat',
        component: Chat,
        meta: { requiresAuth: true }
    }
];

const router = createRouter({
    history: createWebHistory(""),
    routes
});

router.beforeEach((to, from, next) => {
    const store = userdataStore();

    if (to.matched.some(record => record.meta.requiresAuth)) {
        if (!store.getAccessToken()) {
            next({ name: 'Login' });
        } else {
            next();
        }
    } else {
        next();
    }
});

export default router;