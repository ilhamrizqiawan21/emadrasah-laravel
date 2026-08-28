import { Head, Link, router } from '@inertiajs/react';
import { ArrowUpDown, Edit, Loader2, Plus, Search, Trash2 } from 'lucide-react';
import { FormEvent, useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Pagination } from '@/Components/Pagination';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Select } from '@/Components/ui/select';
import { Table, TableContainer, TBody, Td, Th, THead, Tr } from '@/Components/ui/table';
import { Paginated, UserRole } from '@/types';

interface UserRow {
    id: number;
    name: string;
    email: string;
    role: UserRole;
    is_active: boolean;
}

interface UsersIndexProps {
    users: Paginated<UserRow>;
    filters: {
        search?: string;
        role?: string;
        status?: string;
        sort?: string;
        direction?: string;
    };
    roles: Record<UserRole, string>;
}

const roleLabels: Record<UserRole, string> = {
    admin: 'Admin',
    guru: 'Guru',
    wali_murid: 'Wali Murid',
    siswa: 'Siswa',
    operator: 'Operator',
};

export default function UsersIndex({ users, filters, roles }: UsersIndexProps) {
    const [tableFilters, setTableFilters] = useState({
        search: filters.search ?? '',
        role: filters.role ?? '',
        status: filters.status ?? '',
    });
    const [loading, setLoading] = useState(false);

    const applyFilters = (event?: FormEvent) => {
        event?.preventDefault();
        setLoading(true);
        router.get('/users', tableFilters, {
            preserveState: true,
            replace: true,
            onFinish: () => setLoading(false),
        });
    };

    const sortBy = (column: string) => {
        const direction = filters.sort === column && filters.direction === 'asc' ? 'desc' : 'asc';
        setLoading(true);
        router.get('/users', { ...tableFilters, sort: column, direction }, {
            preserveState: true,
            replace: true,
            onFinish: () => setLoading(false),
        });
    };

    const sortLabel = (label: string, column: string) => {
        const active = filters.sort === column;

        return (
            <button
                type="button"
                className="inline-flex items-center gap-1 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                onClick={() => sortBy(column)}
                aria-label={`Urutkan berdasarkan ${label}`}
            >
                {label}
                <ArrowUpDown size={13} className={active ? 'text-blue-600' : 'text-slate-400'} />
            </button>
        );
    };

    const destroy = (user: UserRow) => {
        if (window.confirm(`Hapus user ${user.name}?`)) {
            router.delete(`/users/${user.id}`);
        }
    };

    return (
        <AppLayout title="Pengguna">
            <Head title="Pengguna" />
            <Card>
                <CardHeader className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <CardTitle>Manajemen Pengguna</CardTitle>
                    <Link href="/users/create">
                        <Button>
                            <Plus size={17} />
                            Tambah User
                        </Button>
                    </Link>
                </CardHeader>
                <CardContent>
                    <form className="mb-4 flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center" onSubmit={applyFilters}>
                        <div className="flex min-w-0 items-center gap-2 sm:min-w-72">
                            <Search size={18} className="text-slate-400" />
                            <Input
                                value={tableFilters.search}
                                placeholder="Cari nama atau email"
                                onChange={(event) => setTableFilters((current) => ({ ...current, search: event.target.value }))}
                            />
                        </div>
                        <Select
                            className="max-w-48"
                            value={tableFilters.role}
                            onChange={(event) => setTableFilters((current) => ({ ...current, role: event.target.value }))}
                        >
                            <option value="">Semua Role</option>
                            {Object.entries(roles).map(([value, label]) => (
                                <option key={value} value={value}>
                                    {label}
                                </option>
                            ))}
                        </Select>
                        <Select
                            className="max-w-44"
                            value={tableFilters.status}
                            onChange={(event) => setTableFilters((current) => ({ ...current, status: event.target.value }))}
                        >
                            <option value="">Semua Status</option>
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </Select>
                        <Button variant="outline" type="submit" disabled={loading}>
                            {loading ? <Loader2 size={16} className="animate-spin" /> : null}
                            Filter
                        </Button>
                        <Button variant="ghost" type="button" onClick={() => router.get('/users')}>
                            Reset
                        </Button>
                    </form>
                    <TableContainer>
                        {loading ? <div className="absolute inset-0 z-10 grid place-items-center bg-white/70 text-sm font-semibold text-slate-600">Memuat data...</div> : null}
                        <Table>
                            <THead>
                                <Tr>
                                    <Th>{sortLabel('Nama', 'name')}</Th>
                                    <Th>{sortLabel('Email', 'email')}</Th>
                                    <Th>{sortLabel('Role', 'role')}</Th>
                                    <Th>{sortLabel('Status', 'is_active')}</Th>
                                    <Th className="w-32 text-right">Aksi</Th>
                                </Tr>
                            </THead>
                            <TBody>
                                {users.data.length > 0 ? (
                                    users.data.map((user) => (
                                        <Tr key={user.id}>
                                            <Td className="font-semibold text-slate-900">{user.name}</Td>
                                            <Td>{user.email}</Td>
                                            <Td>{roleLabels[user.role]}</Td>
                                            <Td>
                                                <Badge variant={user.is_active ? 'success' : 'danger'}>
                                                    {user.is_active ? 'Aktif' : 'Nonaktif'}
                                                </Badge>
                                            </Td>
                                            <Td>
                                                <div className="flex justify-end gap-2">
                                                    <Link href={`/users/${user.id}/edit`}>
                                                        <Button variant="outline" size="icon" title="Edit" aria-label={`Edit user ${user.name}`}>
                                                            <Edit size={16} />
                                                        </Button>
                                                    </Link>
                                                    <Button variant="danger" size="icon" title="Hapus" onClick={() => destroy(user)} aria-label={`Hapus user ${user.name}`}>
                                                        <Trash2 size={16} />
                                                    </Button>
                                                </div>
                                            </Td>
                                        </Tr>
                                    ))
                                ) : (
                                    <Tr>
                                        <Td colSpan={5} className="py-10 text-center text-sm font-semibold text-slate-500">
                                            Belum ada pengguna yang cocok dengan filter.
                                        </Td>
                                    </Tr>
                                )}
                            </TBody>
                        </Table>
                    </TableContainer>
                    <div className="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="text-sm font-medium text-slate-500">
                            Menampilkan {users.from ?? 0}-{users.to ?? 0} dari {users.total} user
                        </div>
                        <Pagination links={users.links} />
                    </div>
                </CardContent>
            </Card>
        </AppLayout>
    );
}
