import { defineStore } from 'pinia';
import api from '../api';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        token: localStorage.getItem('eyescomp_token') || null,
        user: JSON.parse(localStorage.getItem('eyescomp_user') || 'null'),
        currentStore: JSON.parse(localStorage.getItem('eyescomp_current_store') || 'null'),
        isLoading: false,
        error: null,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
        tenant: (state) => state.user?.tenant || null,
        roles: (state) => state.user?.roles?.map((r) => r.name) || [],
        isOwner: (state) => state.roles.includes('owner'),
        isManager: (state) => state.roles.includes('manager'),
        isCashier: (state) => state.roles.includes('cashier'),
    },

    actions: {
        setAuthData(token, user, currentStore) {
            this.token = token;
            this.user = user;
            this.currentStore = currentStore || user?.stores?.[0] || null;

            localStorage.setItem('eyescomp_token', token);
            localStorage.setItem('eyescomp_user', JSON.stringify(user));
            if (this.currentStore) {
                localStorage.setItem('eyescomp_current_store', JSON.stringify(this.currentStore));
            }
        },

        async registerOwner(form) {
            this.isLoading = true;
            this.error = null;
            try {
                const response = await api.post('/auth/register-owner', form);
                const { token, user, current_store } = response.data;
                this.setAuthData(token, user, current_store);
                return response.data;
            } catch (err) {
                this.error = err.response?.data?.message || 'Erreur lors de la création du compte.';
                throw err;
            } finally {
                this.isLoading = false;
            }
        },

        async login(credentials) {
            this.isLoading = true;
            this.error = null;
            try {
                const response = await api.post('/auth/login', credentials);
                const { token, user, current_store } = response.data;
                this.setAuthData(token, user, current_store);
                return response.data;
            } catch (err) {
                this.error = err.response?.data?.message || 'Identifiants incorrects.';
                throw err;
            } finally {
                this.isLoading = false;
            }
        },

        async pinLogin(pinCredentials) {
            this.isLoading = true;
            this.error = null;
            try {
                const response = await api.post('/auth/pin-login', pinCredentials);
                const { token, user, current_store } = response.data;
                this.setAuthData(token, user, current_store);
                return response.data;
            } catch (err) {
                this.error = err.response?.data?.message || 'Code PIN invalide.';
                throw err;
            } finally {
                this.isLoading = false;
            }
        },

        async fetchMe() {
            if (!this.token) return null;
            try {
                const response = await api.get('/auth/me');
                this.user = response.data.user;
                this.currentStore = response.data.current_store;
                localStorage.setItem('eyescomp_user', JSON.stringify(this.user));
                if (this.currentStore) {
                    localStorage.setItem('eyescomp_current_store', JSON.stringify(this.currentStore));
                }
                return this.user;
            } catch (err) {
                this.logout();
                throw err;
            }
        },

        async logout() {
            try {
                if (this.token) {
                    await api.post('/auth/logout');
                }
            } catch (e) {
                // Ignore API error on logout
            } finally {
                this.token = null;
                this.user = null;
                this.currentStore = null;
                localStorage.removeItem('eyescomp_token');
                localStorage.removeItem('eyescomp_user');
                localStorage.removeItem('eyescomp_current_store');
            }
        },
    },
});
