import { authRepository } from "@/repositories/authRepository";
import type { AuthPayload, User } from "@/types/api";
import { defineStore } from "pinia";

const TOKEN_STORAGE_KEY = 'rick_and_morty_token';
const USER_STORAGE_KEY = 'rick_and_morty_user';

interface AuthState {
    user: User | null;
    token: string | null;
}

export const useAuthStore = defineStore('auth', {
    state: (): AuthState => ({
        user: JSON.parse(localStorage.getItem(USER_STORAGE_KEY) ?? 'null'),
        token: localStorage.getItem(TOKEN_STORAGE_KEY),
    }),

    getters:{
        isAuthenticated: (state) => state.token !== null
    },

    actions: {
        setSession(payload: AuthPayload['data']) {
            this.user = payload.user;
            this.token = payload.token;
            localStorage.setItem(TOKEN_STORAGE_KEY, payload.token);
            localStorage.setItem(USER_STORAGE_KEY, JSON.stringify(payload.user));
        },

        clearSession() {
            this.user = null;
            this.token = null;
            localStorage.removeItem(TOKEN_STORAGE_KEY);
            localStorage.removeItem(USER_STORAGE_KEY);
        },

        async register(payload: { name: string; email: string; password: string; password_confirmation: string }) {
            const session = await authRepository.register(payload);
            this.setSession(session);
        },

        async login(payload: { email: string; password: string }) {
           const session = await authRepository.login(payload);
            this.setSession(session);
        },

        async logout() {
            try {
                await authRepository.logout();
            } finally {
                this.clearSession();
            }
        },
    }
})
