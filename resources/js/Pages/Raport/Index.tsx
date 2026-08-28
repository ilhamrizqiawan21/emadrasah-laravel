import { Head, Link, router } from '@inertiajs/react';
import { FilePenLine, Loader2, Search } from 'lucide-react';
import { FormEvent, useState } from 'react';
import { DataTable, DataTableColumn } from '@/Components/DataTable';
import AppLayout from '@/Layouts/AppLayout';
import { Pagination } from '@/Components/Pagination';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Select } from '@/Components/ui/select';
import { Paginated } from '@/types';

interface KelasOption {
    id: number;
    nama_kelas: string;
}

interface RaportSiswaRow {
    id: number;
    nis: string;
    nisn: string | null;
    nama_lengkap: string;
    status: string | null;
    raport_nilai_count: number;
    kelas: KelasOption | null;
}

interface RaportIndexProps {
    siswa: Paginated<RaportSiswaRow>;
    kelas: KelasOption[];
    filters: {
        search?: string;
        status?: string;
        kelas_id?: string;
        sort?: string;
        direction?: string;
    };
}

const statuses = ['Aktif', 'Lulus', 'Pindah', 'Keluar', 'Meninggal'];

function statusVariant(status?: string | null) {
    if (status === 'Aktif') {
        return 'success';
    }

    if (status === 'Keluar' || status === 'Meninggal') {
        return 'danger';
    }

    return 'warning';
}

export default function RaportIndex({ siswa, kelas, filters }: RaportIndexProps) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState(filters.status ?? '');
    const [kelasId, setKelasId] = useState(filters.kelas_id ?? '');
    const [loading, setLoading] = useState(false);

    const query = {
        search,
        status,
        kelas_id: kelasId,
    };

    const applyFilters = (event?: FormEvent) => {
        event?.preventDefault();
        setLoading(true);
        router.get('/raport', query, {
            preserveState: true,
            replace: true,
            onFinish: () => setLoading(false),
        });
    };

    const sortBy = (column: string) => {
        const direction = filters.sort === column && filters.direction === 'asc' ? 'desc' : 'asc';
        setLoading(true);
        router.get('/raport', { ...query, sort: column, direction }, {
            preserveState: true,
            replace: true,
            onFinish: () => setLoading(false),
        });
    };

    const hasFilters = Boolean(filters.search || filters.status || filters.kelas_id || filters.sort);

    const columns: Array<DataTableColumn<RaportSiswaRow>> = [
        {
            key: 'nis',
            label: 'NIS / NISN',
            sortable: true,
            render: (item) => (
                <div>
                    <div className="font-semibold text-blue-700">{item.nis}</div>
                    <div className="mt-1 text-xs font-medium text-slate-500">NISN: {item.nisn ?? '-'}</div>
                </div>
            ),
        },
        {
            key: 'nama_lengkap',
            label: 'Nama Siswa',
            sortable: true,
            render: (item) => <div className="font-semibold text-slate-900">{item.nama_lengkap}</div>,
        },
        {
            key: 'kelas',
            label: 'Kelas',
            sortable: true,
            render: (item) => <Badge>{item.kelas?.nama_kelas ?? '-'}</Badge>,
        },
        {
            key: 'status',
            label: 'Status',
            sortable: true,
            render: (item) => <Badge variant={statusVariant(item.status)}>{item.status ?? 'Aktif'}</Badge>,
        },
        {
            key: 'raport_nilai_count',
            label: 'Nilai Tersimpan',
            headerClassName: 'w-40',
            render: (item) => (
                <span className="font-semibold text-slate-700">
                    {item.raport_nilai_count} entri
                </span>
            ),
        },
        {
            key: 'actions',
            label: 'Aksi',
            headerClassName: 'w-36 text-right',
            className: 'text-right',
            render: (item) => (
                <Link href={`/raport/${item.id}/manage`}>
                    <Button variant="outline" size="sm" aria-label={`Kelola nilai raport ${item.nama_lengkap}`}>
                        <FilePenLine size={16} />
                        Kelola
                    </Button>
                </Link>
            ),
        },
    ];

    return (
        <AppLayout title="Arsip Nilai Raport">
            <Head title="Arsip Nilai Raport" />
            <Card>
                <CardHeader>
                    <CardTitle>Arsip Nilai Raport</CardTitle>
                    <p className="mt-1 text-sm font-medium text-slate-500">Kelola nilai akademik siswa untuk integrasi Buku Induk.</p>
                </CardHeader>
                <CardContent>
                    <form className="mb-4 grid gap-2 lg:grid-cols-[minmax(220px,1fr)_180px_180px_auto_auto]" onSubmit={applyFilters}>
                        <div className="flex min-w-0 items-center gap-2">
                            <Search size={18} className="shrink-0 text-slate-400" />
                            <Input
                                type="search"
                                value={search}
                                placeholder="Cari nama, NIS, atau NISN"
                                onChange={(event) => setSearch(event.target.value)}
                            />
                        </div>
                        <Select value={status} onChange={(event) => setStatus(event.target.value)} aria-label="Filter status siswa">
                            <option value="">Semua Status</option>
                            {statuses.map((item) => (
                                <option key={item} value={item}>
                                    {item}
                                </option>
                            ))}
                        </Select>
                        <Select value={kelasId} onChange={(event) => setKelasId(event.target.value)} aria-label="Filter kelas">
                            <option value="">Semua Kelas</option>
                            {kelas.map((item) => (
                                <option key={item.id} value={item.id}>
                                    {item.nama_kelas}
                                </option>
                            ))}
                        </Select>
                        <Button variant="outline" type="submit" disabled={loading}>
                            {loading ? <Loader2 size={16} className="animate-spin" /> : null}
                            Filter
                        </Button>
                        {hasFilters ? (
                            <Button variant="ghost" type="button" onClick={() => router.get('/raport')}>
                                Reset
                            </Button>
                        ) : null}
                    </form>

                    <DataTable
                        data={siswa.data}
                        columns={columns}
                        loading={loading}
                        sort={filters.sort}
                        direction={filters.direction}
                        onSort={sortBy}
                        emptyTitle="Data siswa tidak ditemukan"
                        emptyDescription="Ubah filter untuk melihat siswa yang akan dikelola rapornya."
                    />

                    <div className="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="text-sm font-medium text-slate-500">
                            Menampilkan {siswa.from ?? 0}-{siswa.to ?? 0} dari {siswa.total} siswa
                        </div>
                        <Pagination links={siswa.links} />
                    </div>
                </CardContent>
            </Card>
        </AppLayout>
    );
}
