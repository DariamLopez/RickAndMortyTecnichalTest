import http from "@/lib/http";
import { AuthPayload } from "@/types/api";


export interface RegisterPayload {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
}

export interface LoginPayload{
    email: string;
    password: string;
}

export const authRepository = {
    async register(payload: RegisterPayload) {
        const response = await http.post<AuthPayload>("/register", payload);
        return response.data;
    },
    async login(payload: LoginPayload) {
        const response = await http.post<AuthPayload>("/login", payload);
        return response.data;
    },
    async logout(): Promise<void> {
        await http.post("/logout");
    }
}
