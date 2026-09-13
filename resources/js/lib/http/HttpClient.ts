export interface RequestConfig {
    params?: object;
    headers?: Record<string, string>;
}

export interface HttpClient {
    get<T>(url: string, config?: RequestConfig): Promise<T>;
    post<T>(url: string, body?: unknown, config?: RequestConfig): Promise<T>;
    put<T>(url: string, body?: unknown, config?: RequestConfig): Promise<T>;
    patch<T>(url: string, body?: unknown, config?: RequestConfig): Promise<T>;
    delete<T>(url: string, config?: RequestConfig): Promise<T>;
}
