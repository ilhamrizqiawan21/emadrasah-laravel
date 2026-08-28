import { UserRole } from '@/types';

export const roleLabels: Record<UserRole, string> = {
    admin: 'Admin',
    operator: 'Operator',
    guru: 'Guru',
    siswa: 'Siswa',
    wali_murid: 'Wali Murid',
};

export function formatDate(value: string | null | undefined) {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(value));
}

export function formatNumber(value: number | null | undefined) {
    return new Intl.NumberFormat('id-ID').format(value ?? 0);
}

export function formatStatus(value: string | null | undefined) {
    if (!value) {
        return '-';
    }

    return value
        .split('_')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
}
