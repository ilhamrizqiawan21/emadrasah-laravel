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
import { Paginated } from '@/types';

interface TahunPelajaranRow {
    id: number;
    kode: string;
    nama: string;
    is_aktif: boolean;
}

interface TahunPelajaranIndexProps {
    tahunPelajaran: Paginated<TahunPelajaranRow>;
    filters: {
        search?: string;
        sort?: string;
        direction?: string;
    };
}

export default function TahunPelajaranIndex({ tahunPelajaran, filters }: TahunPelajaranIndexProps) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [loading, setLoading] = useState(false);
    const [selectedTahun, setSelectedTahun] = useState<TahunPelajaranRow | null>(null);

    const applyFilters = (event?: FormEvent) => {
        event?.preventDefault();
        setLoading(true);
        router.get('/tahun-pelajaran', { search }, {
            preserveState: true,
            replace: true,
            onFinish: () => setLoading(false),
        });
    };

    const sortBy = (column: string) => {
        const direction = filters.sort === column && filters.direction === 'asc' ? 'desc' : 'asc';
        setLoading(true);
        router.get('/tahun-pelajaran', { search, sort: column, direction }, {
            preserveState: true,
            replace: true,
            onFinish: () => setLoading(false),
        });
    };

    const destroy = () => {
        if (!selectedTahun) {
            return;
        }

        router.delete(`/tahun-pelajaran/${selectedTahun.id}`, {
            onFinish: () => setSelectedTahun(null),
        });
    };

    const columns: Array<DataTableColumn<TahunPelajaranRow>> = [
        {
            key: 'kode',
            label: 'Kode',
            sortable: true,
            render: (item) => <span className="font-semibold text-slate-900">{item.kode}</span>,
        },
        {
            key: 'nama',
            label: 'Nama',
            sortable: true,
            render: (item) => item.nama,
        },
        {
            key: 'is_aktif',
            label: 'Status',
            sortable: true,
            render: (item) => <Badge variant={item.is_aktif ? 'success' : 'default'}>{item.is_aktif ? 'Aktif' : 'Tidak aktif'}</Badge>,
        },
        {
            key: 'actions',
            label: 'Aksi',
            headerClassName: 'w-32 text-right',
            className: 'text-right',
            render: (item) => (
                <div className="flex justify-end gap-2">
                    <Link href={`/tahun-pelajaran/${item.id}/edit`}>
                        <Button variant="outline" size="icon" title="Edit" aria-label={`Edit tahun pelajaran ${item.kode}`}>
                            <Edit size={16} />
                        </Button>
                    </Link>
                    <Button variant="danger" size="icon" title="Hapus" aria-label={`Hapus tahun pelajaran ${item.kode}`} onClick={() => setSelectedTahun(item)}>
                        <Trash2 size={16} />
                    </Button>
                </div>
            ),
        },
    ];

    return (
        <AppLayout title="Tahun Pelajaran">
            <Head title="Tahun Pelajaran" />
            <Card>
                <CardHeader className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <CardTitle>Tahun Pelajaran</CardTitle>
                        <p className="mt-1 text-sm font-medium text-slate-500">Kelola tahun pelajaran untuk rapor, jadwal, dan arsip.</p>
                    </div>
                    <Link href="/tahun-pelajaran/create">
                        <Button>
                            <Plus size={17} />
                            Tambah Tahun
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
                                placeholder="Cari kode atau nama tahun pelajaran"
                                onChange={(event) => setSearch(event.target.value)}
                            />
                        </div>
                        <Button variant="outline" type="submit" disabled={loading}>
                            {loading ? <Loader2 size={16} className="animate-spin" /> : null}
                            Cari
                        </Button>
                        {filters.search ? (
                            <Button variant="ghost" type="button" onClick={() => router.get('/tahun-pelajaran')}>
                                Reset
                            </Button>
                        ) : null}
                    </form>

                    <DataTable
                        data={tahunPelajaran.data}
                        columns={columns}
                        loading={loading}
                        sort={filters.sort}
                        direction={filters.direction}
                        onSort={sortBy}
                        emptyTitle="Belum ada tahun pelajaran"
                        emptyDescription="Tahun pelajaran yang cocok dengan filter akan tampil di sini."
                    />

                    <div className="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="text-sm font-medium text-slate-500">
                            Menampilkan {tahunPelajaran.from ?? 0}-{tahunPelajaran.to ?? 0} dari {tahunPelajaran.total} tahun pelajaran
                        </div>
                        <Pagination links={tahunPelajaran.links} />
                    </div>
                </CardContent>
            </Card>

            <ConfirmDeleteDialog
                open={Boolean(selectedTahun)}
                message={`Hapus tahun pelajaran ${selectedTahun?.kode ?? ''}? Pastikan tidak sedang dipakai data aktif.`}
                onClose={() => setSelectedTahun(null)}
                onConfirm={destroy}
            />
        </AppLayout>
    );
}
