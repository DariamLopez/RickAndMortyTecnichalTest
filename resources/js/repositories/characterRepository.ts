import http from "@/lib/http";
import { Character, CharacterDetail, CharacterFilters, Paginated } from "@/types/api";

export const characterRepository = {
    async list(filters: CharacterFilters = {}): Promise<Paginated<Character>> {
        return http.get<Paginated<Character>>("/characters", { params: filters });

    },
    async show(id: number): Promise<CharacterDetail> {
        const response = await http.get<{ data: CharacterDetail }>(`/characters/${id}`);
        return response.data;
    },
}
