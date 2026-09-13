import { beforeEach, describe, expect, it, vi } from 'vitest';
import { createPinia, setActivePinia } from 'pinia';
import { useAuthStore } from '@/stores/auth';
import { authRepository } from '@/repositories/authRepository';

vi.mock('@/repositories/authRepository', () => ({
    authRepository: {
        login: vi.fn(),
        register: vi.fn(),
        logout: vi.fn(),
    },
}));

describe('useAuthStore', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        localStorage.clear();
        vi.clearAllMocks();
    });

    it('starts with no session when localStorage is empty', () => {
        const store = useAuthStore();

        expect(store.isAuthenticated).toBe(false);
        expect(store.user).toBeNull();
    });

    it('persists the session to localStorage after a successful login', async () => {
        vi.mocked(authRepository.login).mockResolvedValue({
            user: { id: 1, name: 'Rick Sanchez', email: 'rick@citadel.test' },
            token: 'plain-text-token',
        });

        const store = useAuthStore();
        await store.login({ email: 'rick@citadel.test', password: 'secret' });

        expect(store.isAuthenticated).toBe(true);
        expect(store.token).toBe('plain-text-token');
        expect(localStorage.getItem('rick_and_morty_token')).toBe('plain-text-token');
    });

    it('hydrates the session from localStorage when the store is created', () => {
        localStorage.setItem('rick_and_morty_token', 'stored-token');
        localStorage.setItem(
            'rick_and_morty_user',
            JSON.stringify({ id: 2, name: 'Morty', email: 'morty@test.io' }),
        );

        const store = useAuthStore();

        expect(store.isAuthenticated).toBe(true);
        expect(store.user?.name).toBe('Morty');
    });

    it('clears the session and localStorage on logout, even if the API call fails', async () => {
        vi.mocked(authRepository.logout).mockRejectedValue(new Error('network error'));

        const store = useAuthStore();
        store.setSession({ user: { id: 1, name: 'Rick', email: 'rick@citadel.test' }, token: 'abc' });

        await expect(store.logout()).rejects.toThrow();

        expect(store.isAuthenticated).toBe(false);
        expect(localStorage.getItem('rick_and_morty_token')).toBeNull();
    });
});
