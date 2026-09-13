export interface User {
    id: number,
    name: string,
    email: string,
}

export interface AuthPayload {
    data: {
        user: User;
        token: string;
    };
}

export interface Location {
    id: number;
    name: string;
    type: string;
    dimension: string;
}

export interface Episode {
    id: number;
    name: string;
    air_date: string;
    episode_code: string;
}

export type CharacterStatus = 'Alive' | 'Dead' | 'Unknown';
export type CharacterGender = 'Male' | 'Female' | 'Genderless' | 'Unknown';

export interface Character {
    id: number;
    name: string;
    status:CharacterStatus;
    species: string;
    type: string;
    gender: CharacterGender;
    image:string;
}

export interface CharacterDetail extends Character {
    origin: Location | null;
    location: Location | null;
    episode: Episode[];
}

export interface CharacterFilters {
    name?: string;
    status?: CharacterStatus;
    species?: string;
    gender?: CharacterGender;
    page?: number;
    per_page?: number;
}

export interface PaginationLinks {
    first: string | null;
    last: string | null;
    prev: string | null;
    next: string | null;
}

export interface PaginationMeta {
    current_page: number;
    from: number | null;
    last_page: number;
    per_page: number;
    to: number | null;
    total: number;
}

export interface Paginated<T> {
    data: T[];
    links: PaginationLinks;
    meta: PaginationMeta;
}

export interface ApiErrorResponse {
    message: string;
    errors?: Record<string, string[]>;
}
