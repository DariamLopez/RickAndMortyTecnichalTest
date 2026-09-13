import router from "@/router";
import { useAuthStore } from "@/stores/auth";
import { ApiErrorResponse } from "@/types/api";
import axios, { AxiosError, AxiosInstance } from "axios";
import { HttpClient, RequestConfig } from "./HttpClient";
import { ApiError } from "./ApiError";

export class AxiosHttpClient implements HttpClient {
    private readonly instance: AxiosInstance;
    constructor(baseUrl: string) {
        this.instance = axios.create({
            baseURL: import.meta.env.VITE_API_BASE_URL,
            headers: {
                Accept: "application/json",
            },
        });
        this.instance.interceptors.request.use((config) => {
            const authStore = useAuthStore();

            if (authStore.token) {
                config.headers.Authorization = `Bearer ${authStore.token}`;
            }
            return config;
        });
        this.instance.interceptors.response.use(
            (response) => response,
            (error: AxiosError<ApiErrorResponse>) => {
                if (error.response?.status === 401) {
                    const authStore = useAuthStore();
                    const wasAuthenticated = authStore.isAuthenticated;
                    authStore.clearSession();

                    if (wasAuthenticated) {
                        router.push({
                            name: "login",
                            query: { sessionExpired: "1" },
                        });
                    }
                }
                return Promise.reject(
                    new ApiError(
                        error.response?.data?.message ?? "An unknown error occurred",
                        error.response?.status ?? 500,
                        error.response?.data?.errors,
                    )
                );
            },
        );
    }
    async get<T>(url: string, config?: RequestConfig): Promise<T> {
        return (await this.instance.get<T>(url, config)).data;
    }
    async post<T>(url: string, body?: unknown, config?: RequestConfig): Promise<T> {
        return (await this.instance.post<T>(url, body, config)).data;
    }
    async put<T>(url: string, body?: unknown, config?: RequestConfig): Promise<T> {
        return (await this.instance.put<T>(url, body, config)).data;
    }
    async patch<T>(url: string, body?: unknown, config?: RequestConfig): Promise<T> {
        return (await this.instance.patch<T>(url, body, config)).data;
    }
    async delete<T>(url: string, config?: RequestConfig): Promise<T> {
        return (await this.instance.delete<T>(url, config)).data;
    }
}
