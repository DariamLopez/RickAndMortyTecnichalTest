import { AxiosHttpClient } from "./AxiosHttpClient";
import { HttpClient } from "./HttpClient";


const http: HttpClient = new AxiosHttpClient(import.meta.env.VITE_API_BASE_URL);

export default http;
export type { HttpClient, RequestConfig } from './HttpClient';
