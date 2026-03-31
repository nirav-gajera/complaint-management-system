import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        initialized: false,
        loading: false,
    }),
    getters: {
        isAuthenticated: (state) => !!state.user,
        isStaff: (state) => !!state.user?.is_staff,
    },
    actions: {
        async initialize() {
            if (this.initialized) {
                return;
            }

            this.loading = true;

            try {
                const { data } = await axios.get('/api/auth/user');
                this.user = data.user;
            } catch (error) {
                this.user = null;
            } finally {
                this.initialized = true;
                this.loading = false;
            }
        },
        async login(payload) {
            await axios.get('/sanctum/csrf-cookie');
            const { data } = await axios.post('/api/auth/login', payload);
            this.user = data.user;
            return data;
        },
        async register(payload) {
            await axios.get('/sanctum/csrf-cookie');
            const { data } = await axios.post('/api/auth/register', payload);
            this.user = data.user;
            return data;
        },
        async logout() {
            try {
                await axios.get('/sanctum/csrf-cookie');
                await axios.post('/api/auth/logout');
            } catch (error) {
                if ([401, 419].includes(error?.response?.status)) {
                    this.user = null;
                    return;
                }

                throw error;
            }

            this.user = null;
        },
        async refresh() {
            const { data } = await axios.get('/api/auth/user');
            this.user = data.user;
            return data.user;
        },
    },
});