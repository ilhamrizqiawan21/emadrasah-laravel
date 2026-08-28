export type UserRole = 'admin' | 'guru' | 'wali_murid' | 'siswa' | 'operator';

export interface AuthUser {
    id: number;
    name: string;
    email: string;
    role: UserRole;
    is_active: boolean;
    permissions: string[];
}

export interface Flash {
    success?: string;
    error?: string;
}

export interface PageProps {
    auth: {
        user: AuthUser | null;
    };
    flash: Flash;
    [key: string]: unknown;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface Paginated<T> {
    data: T[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
}
