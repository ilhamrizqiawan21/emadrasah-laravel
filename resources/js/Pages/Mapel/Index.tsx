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

interface MapelRow {
    id: number;
    nama_mapel: string;
    jp_per_sesi: number | null;
    parent_id: number | null;
    parent: { id: number; nama_mapel: string } | null;
    urut: number | null;
}

interface MapelIndexProps {
    mapels: Paginated<MapelRow>;
    filters: {
        search?: string;
        sort?: string;
        direction?: string;
    };
}

export default function MapelIndex({ mapels, filters }: MapelIndexProps) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [loading, setLoading] = useState(false);
    const [selectedMapel, setSelectedMapel] = useState<MapelRow | null>(null);

    const applyFilters = (event?: FormEvent) => {
        event?.preventDefault();
        setLoading(true);
        router.get('/mapel', { search }, {
            preserveState: true,
            replace: true,
            onFinish: () => setLoading(false),
        });
    };

    const sortBy = (column: string) => {
        const direction = filters.sort === column && filters.direction === 'asc' ? 'desc' : 'asc';
        setLoading(true);
        router.get('/mapel', { search, sort: column, direction }, {
            preserveState: true,
            replace: true,
            onFinish: () => setLoading(false),
        });
    };

    const destroy = () => {
        if (!selectedMapel) {
            return;
        }

        router.delete(`/mapel/${selectedMapel.id}`, {
            onFinish: () => setSelectedMapel(null),
        });
    };

    const columns: Array<DataTableColumn<MapelRow>> = [
        {
            key: 'nama_mapel',
            label: 'Nama Mata Pelajaran',
            sortable: true,
            render: (item) => <span className="font-semibold text-slate-900">{item.nama_mapel}</span>,
        },
        {
            key: 'parent',
            label: 'Kelompok',
            render: (item) => item.parent ? <Badge variant="info">{item.parent.nama_mapel}</Badge> : <Badge>Induk</Badge>,
        },
        {
            key: 'jp_per_sesi',
            label: 'JP / Sesi',
            sortable: true,
            render: (item) => formatNumber(item.jp_per_sesi),
        },
        {
            key: 'urut',
            label: 'Urutan',
            sortable: true,
            render: (item) => formatNumber(item.urut),
        },
        {
            key: 'actions',
            label: 'Aksi',
            headerClassName: 'w-32 text-right',
            className: 'text-right',
            render: (item) => (
                <div className="flex justify-end gap-2">
                    <Link href={`/mapel/${item.id}/edit`}>
                        <Button variant="outline" size="icon" title="Edit" aria-label={`Edit mata pelajaran ${item.nama_mapel}`}>
                            <Edit size={16} />
                        </Button>
                    </Link>
                    <Button variant="danger" size="icon" title="Hapus" aria-label={`Hapus mata pelajaran ${item.nama_mapel}`} onClick={() => setSelectedMapel(item)}>
                        <Trash2 size={16} />
                    </Button>
                </div>
            ),
        },
    ];

    return (
        <AppLayout title="Mata Pelajaran">
            <Head title="Mata Pelajaran" />
            <Card>
                <CardHeader className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <CardTitle>Mata Pelajaran</CardTitle>
                        <p className="mt-1 text-sm font-medium text-slate-500">Kelola daftar mapel, kelompok mapel, JP per sesi, dan urutan.</p>
                    </div>
                    <Link href="/mapel/create">
                        <Button>
                            <Plus size={17} />
                            Tambah Mapel
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
                                placeholder="Cari mata pelajaran atau kelompok"
                                onChange={(event) => setSearch(event.target.value)}
                            />
                        </div>
                        <Button variant="outline" type="submit" disabled={loading}>
                            {loading ? <Loader2 size={16} className="animate-spin" /> : null}
                            Cari
                        </Button>
                        {filters.search ? (
                            <Button variant="ghost" type="button" onClick={() => router.get('/mapel')}>
                                Reset
                            </Button>
                        ) : null}
                    </form>

                    <DataTable
                        data={mapels.data}
                        columns={columns}
                        loading={loading}
                        sort={filters.sort}
                        direction={filters.direction}
                        onSort={sortBy}
                        emptyTitle="Belum ada data mata pelajaran"
                        emptyDescription="Mata pelajaran yang cocok dengan filter akan tampil di sini."
                    />

                    <div className="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="text-sm font-medium text-slate-500">
                            Menampilkan {mapels.from ?? 0}-{mapels.to ?? 0} dari {mapels.total} mapel
                        </div>
                        <Pagination links={mapels.links} />
                    </div>
                </CardContent>
            </Card>

            <ConfirmDeleteDialog
                open={Boolean(selectedMapel)}
                message={`Hapus mata pelajaran ${selectedMapel?.nama_mapel ?? ''}? Jadwal terkait dapat ikut terdampak.`}
                onClose={() => setSelectedMapel(null)}
                onConfirm={destroy}
            />
        </AppLayout>
    );
}
