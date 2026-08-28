import { Head, Link, router } from '@inertiajs/react';
import { Edit, Loader2, Plus, Search, Trash2 } from 'lucide-react';
import { FormEvent, useState } from 'react';
import { ConfirmDeleteDialog } from '@/Components/ConfirmDeleteDialog';
import { DataTable, DataTableColumn } from '@/Components/DataTable';
import AppLayout from '@/Layouts/AppLayout';
import { Pagination } from '@/Components/Pagination';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { formatNumber } from '@/lib/format';
import { Paginated } from '@/types';

interface KelasRow {
    id: number;
    nama_kelas: string;
    tingkat: string;
    guru_pembimbing: { id: number; nama: string } | null;
    kapasitas: number | null;
    ruangan: string | null;
    fase: string | null;
}

interface KelasIndexProps {
    kelas: Paginated<KelasRow>;
    filters: {
        search?: string;
        sort?: string;
        direction?: string;
    };
}

export default function KelasIndex({ kelas, filters }: KelasIndexProps) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [loading, setLoading] = useState(false);
    const [selectedKelas, setSelectedKelas] = useState<KelasRow | null>(null);

    const applyFilters = (event?: FormEvent) => {
        event?.preventDefault();
        setLoading(true);
        router.get('/kelas', { search }, {
            preserveState: true,
            replace: true,
            onFinish: () => setLoading(false),
        });
    };

    const sortBy = (column: string) => {
        const direction = filters.sort === column && filters.direction === 'asc' ? 'desc' : 'asc';
        setLoading(true);
        router.get('/kelas', { search, sort: column, direction }, {
            preserveState: true,
            replace: true,
            onFinish: () => setLoading(false),
        });
    };

    const destroy = () => {
        if (!selectedKelas) {
            return;
        }

        router.delete(`/kelas/${selectedKelas.id}`, {
            onFinish: () => setSelectedKelas(null),
        });
    };

    const columns: Array<DataTableColumn<KelasRow>> = [
        {
            key: 'nama_kelas',
            label: 'Nama Kelas',
            sortable: true,
            render: (item) => <Badge variant="info">{item.nama_kelas}</Badge>,
        },
        {
            key: 'tingkat',
            label: 'Tingkat',
            sortable: true,
            render: (item) => item.tingkat,
        },
        {
            key: 'guru_pembimbing',
            label: 'Wali Kelas',
            render: (item) => item.guru_pembimbing?.nama ?? '-',
        },
        {
            key: 'ruangan',
            label: 'Ruangan',
            sortable: true,
            render: (item) => item.ruangan ?? '-',
        },
        {
            key: 'kapasitas',
            label: 'Kapasitas',
            sortable: true,
            render: (item) => formatNumber(item.kapasitas),
        },
        {
            key: 'fase',
            label: 'Fase',
            sortable: true,
            render: (item) => item.fase ? <Badge>{item.fase}</Badge> : '-',
        },
        {
            key: 'actions',
            label: 'Aksi',
            headerClassName: 'w-32 text-right',
            className: 'text-right',
            render: (item) => (
                <div className="flex justify-end gap-2">
                    <Link href={`/kelas/${item.id}/edit`}>
                        <Button variant="outline" size="icon" title="Edit" aria-label={`Edit kelas ${item.nama_kelas}`}>
                            <Edit size={16} />
                        </Button>
                    </Link>
                    <Button variant="danger" size="icon" title="Hapus" aria-label={`Hapus kelas ${item.nama_kelas}`} onClick={() => setSelectedKelas(item)}>
                        <Trash2 size={16} />
                    </Button>
                </div>
            ),
        },
    ];

    return (
        <AppLayout title="Data Kelas">
            <Head title="Data Kelas" />
            <Card>
                <CardHeader className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <CardTitle>Data Kelas</CardTitle>
                        <p className="mt-1 text-sm font-medium text-slate-500">Kelola rombel, wali kelas, ruangan, dan kapasitas.</p>
                    </div>
                    <Link href="/kelas/create">
                        <Button>
                            <Plus size={17} />
                            Tambah Kelas
                        </Button>
                    </Link>
                </CardHeader>
                <CardContent>
                    <form className="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center" onSubmit={applyFilters}>
                        <div className="flex min-w-0 items-center gap-2 sm:min-w-96">
                            <Search size={18} className="text-slate-400" />
                            <Input
                                type="search"
                                value={search}
                                placeholder="Cari kelas, tingkat, wali kelas, atau ruangan"
                                onChange={(event) => setSearch(event.target.value)}
                            />
                        </div>
                        <Button variant="outline" type="submit" disabled={loading}>
                            {loading ? <Loader2 size={16} className="animate-spin" /> : null}
                            Cari
                        </Button>
                        {filters.search ? (
                            <Button variant="ghost" type="button" onClick={() => router.get('/kelas')}>
                                Reset
                            </Button>
                        ) : null}
                    </form>

                    <DataTable
                        data={kelas.data}
                        columns={columns}
                        loading={loading}
                        sort={filters.sort}
                        direction={filters.direction}
                        onSort={sortBy}
                        emptyTitle="Belum ada data kelas"
                        emptyDescription="Kelas yang cocok dengan filter akan tampil di sini."
                    />

                    <div className="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="text-sm font-medium text-slate-500">
                            Menampilkan {kelas.from ?? 0}-{kelas.to ?? 0} dari {kelas.total} kelas
                        </div>
                        <Pagination links={kelas.links} />
                    </div>
                </CardContent>
            </Card>

            <ConfirmDeleteDialog
                open={Boolean(selectedKelas)}
                message={`Hapus kelas ${selectedKelas?.nama_kelas ?? ''}? Sistem akan menolak jika kelas masih memiliki jadwal.`}
                onClose={() => setSelectedKelas(null)}
                onConfirm={destroy}
            />
        </AppLayout>
    );
}
