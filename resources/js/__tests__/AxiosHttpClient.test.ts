import { beforeEach, describe, expect, it, vi } from 'vitest';
import axios from 'axios';
import { createPinia, setActivePinia } from 'pinia';
import { AxiosHttpClient } from '@/lib/http/AxiosHttpClient';
import { ApiError } from '@/lib/http/ApiError';
import { useAuthStore } from '@/stores/auth';

vi.mock('axios');
vi.mock('@/router', () => ({
    default: { push: vi.fn() },
}));

describe('AxiosHttpClient', () => {
    let requestInterceptor: (config: any) => any;
    let responseErrorInterceptor: (error: any) => any;
    let mockInstance: any;

    beforeEach(() => {
        setActivePinia(createPinia());
        localStorage.clear();

        mockInstance = {
            interceptors: {
                request: {
                    use: vi.fn((onFulfilled) => {
                        requestInterceptor = onFulfilled;
                    }),
                },
                response: {
                    use: vi.fn((_onFulfilled, onRejected) => {
                        responseErrorInterceptor = onRejected;
                    }),
                },
            },
            get: vi.fn(),
            post: vi.fn(),
            put: vi.fn(),
            patch: vi.fn(),
            delete: vi.fn(),
        };

        vi.mocked(axios.create).mockReturnValue(mockInstance);
    });

    it('attaches the bearer token from the auth store to outgoing requests', () => {
        const authStore = useAuthStore();
        authStore.setSession({ user: { id: 1, name: 'Rick', email: 'rick@test.io' }, token: 'my-token' });

        new AxiosHttpClient('http://api.test');
        const config = requestInterceptor({ headers: {} });

        expect(config.headers.Authorization).toBe('Bearer my-token');
    });

    it('does not attach an Authorization header when there is no session', () => {
        new AxiosHttpClient('http://api.test');
        const config = requestInterceptor({ headers: {} });

        expect(config.headers.Authorization).toBeUndefined();
    });

    it('clears the session on a 401 response and rejects with an ApiError', async () => {
        const authStore = useAuthStore();
        authStore.setSession({ user: { id: 1, name: 'Rick', email: 'rick@test.io' }, token: 'my-token' });

        new AxiosHttpClient('http://api.test');

        await expect(
            responseErrorInterceptor({
                response: { status: 401, data: { message: 'Unauthenticated.' } },
            }),
        ).rejects.toBeInstanceOf(ApiError);

        expect(authStore.isAuthenticated).toBe(false);
        expect(authStore.token).toBeNull();
    });

    it('normalizes a validation error response into an ApiError with field errors', async () => {
        new AxiosHttpClient('http://api.test');

        await expect(
            responseErrorInterceptor({
                response: {
                    status: 422,
                    data: {
                        message: 'The given data was invalid.',
                        errors: { email: ['The email has already been taken.'] },
                    },
                },
            }),
        ).rejects.toMatchObject({
            message: 'The given data was invalid.',
            status: 422,
            errors: { email: ['The email has already been taken.'] },
        });
    });
});
