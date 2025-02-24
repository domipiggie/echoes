import { createRouter, createWebHistory} from 'vue-router';
import AuthContainer from '../components/AuthContainer.vue';
import Chat from '../components/Chat.vue';

const routes = [
    {
        path: '/',
        name: 'Login',
        component: AuthContainer
    },
    {
        path: '/chat',
        name: 'Chat',
        component: Chat
    }
];

const router = createRouter({
    history: createWebHistory(""),
    routes
});

export default router;