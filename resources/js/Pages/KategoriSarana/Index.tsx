import { Head, Link, router } from '@inertiajs/react';
import { Edit, Loader2, Plus, Search, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { ConfirmDeleteDialog } from '@/Components/ConfirmDeleteDialog';
import { DataTable, DataTableColumn } from '@/Components/DataTable';
import AppLayout from '@/Layouts/AppLayout';
import { Pagination } from '@/Components/Pagination';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { useIndexFilters } from '@/lib/query';
import { Paginated } from '@/types';

interface KategoriSaranaRow {
    id: number;
    nama_kategori: string;
}

interface KategoriSaranaIndexProps {
    kategori: Paginated<KategoriSaranaRow>;
    filters: {
        search?: string;
        sort?: string;
        direction?: string;
    };
}

export default function KategoriSaranaIndex({ kategori, filters }: KategoriSaranaIndexProps) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [deleteTarget, setDeleteTarget] = useState<KategoriSaranaRow | null>(null);
    const { loading, reset, visit } = useIndexFilters({ url: '/kategori-sarana', values: { search }, debounceKeys: ['search'] });

    const destroy = () => {
        if (!deleteTarget) {
            return;
        }

        router.delete(`/kategori-sarana/${deleteTarget.id}`, {
            onFinish: () => setDeleteTarget(null),
        });
    };

    const columns: Array<DataTableColumn<KategoriSaranaRow>> = [
        {
            key: 'nama_kategori',
            label: 'Nama Kategori',
            sortable: true,
            render: (item) => <span className="font-semibold text-slate-900">{item.nama_kategori}</span>,
        },
        {
            key: 'actions',
            label: 'Aksi',
            headerClassName: 'w-32 text-right',
            className: 'text-right',
            render: (item) => (
                <div className="flex justify-end gap-2">
                    <Link href={`/kategori-sarana/${item.id}/edit`}>
                        <Button variant="outline" size="icon" title="Edit" aria-label={`Edit kategori ${item.nama_kategori}`}>
                            <Edit size={16} />
                        </Button>
                    </Link>
                    <Button variant="danger" size="icon" title="Hapus" aria-label={`Hapus kategori ${item.nama_kategori}`} onClick={() => setDeleteTarget(item)}>
                        <Trash2 size={16} />
                    </Button>
                </div>
            ),
        },
    ];

    return (
        <AppLayout title="Kategori Sarana">
            <Head title="Kategori Sarana" />
            <Card>
                <CardHeader className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <CardTitle>Kategori Sarana</CardTitle>
                        <p className="mt-1 text-sm font-medium text-slate-500">Kelola kategori untuk inventaris sarana prasarana.</p>
                    </div>
                    <Link href="/kategori-sarana/create">
                        <Button>
                            <Plus size={17} />
                            Tambah Kategori
                        </Button>
                    </Link>
                </CardHeader>
                <CardContent>
                    <form className="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center" onSubmit={visit}>
                        <div className="flex min-w-0 items-center gap-2 sm:min-w-96">
                            <Search size={18} className="text-slate-400" />
                            <Input type="search" value={search} placeholder="Cari kategori sarana" onChange={(event) => setSearch(event.target.value)} />
                        </div>
                        <Button variant="outline" type="submit" disabled={loading}>
                            {loading ? <Loader2 size={16} className="animate-spin" /> : null}
                            Cari
                        </Button>
                        {filters.search ? <Button variant="ghost" type="button" onClick={reset}>Reset</Button> : null}
                    </form>

                    <DataTable
                        data={kategori.data}
                        columns={columns}
                        loading={loading}
                        sort={filters.sort}
                        direction={filters.direction}
                        onSort={(column) => visit(undefined, { sort: column, direction: filters.sort === column && filters.direction === 'asc' ? 'desc' : 'asc' })}
                        emptyTitle="Belum ada kategori"
                        emptyDescription="Kategori sarana yang cocok dengan filter akan tampil di sini."
                    />

                    <div className="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="text-sm font-medium text-slate-500">
                            Menampilkan {kategori.from ?? 0}-{kategori.to ?? 0} dari {kategori.total} kategori
                        </div>
                        <Pagination links={kategori.links} />
                    </div>
                </CardContent>
            </Card>

            <ConfirmDeleteDialog
                open={Boolean(deleteTarget)}
                message={`Hapus kategori ${deleteTarget?.nama_kategori ?? ''}? Sistem akan menolak jika kategori masih digunakan.`}
                onClose={() => setDeleteTarget(null)}
                onConfirm={destroy}
            />
        </AppLayout>
    );
}
