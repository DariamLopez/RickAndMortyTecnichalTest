import { AxiosHttpClient } from './AxiosHttpClient';
import type { HttpClient, RequestConfig } from './HttpClient';

let instance: HttpClient | null = null;

function resolveClient(): HttpClient {
    if (!instance) {
        instance = new AxiosHttpClient(import.meta.env.VITE_API_BASE_URL);
    }

    return instance;
}

const http: HttpClient = {
    get: (url, config) => resolveClient().get(url, config),
    post: (url, body, config) => resolveClient().post(url, body, config),
    put: (url, body, config) => resolveClient().put(url, body, config),
    patch: (url, body, config) => resolveClient().patch(url, body, config),
    delete: (url, config) => resolveClient().delete(url, config),
};

export default http;
export type { HttpClient, RequestConfig };
