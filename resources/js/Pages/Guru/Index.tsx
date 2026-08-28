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

interface GuruRow {
    id: number;
    kode: string;
    nip: string | null;
    nama: string;
    bidang_studi: string | null;
    beban_jp: number | null;
}

interface GuruIndexProps {
    gurus: Paginated<GuruRow>;
    filters: {
        search?: string;
        sort?: string;
        direction?: string;
    };
}

export default function GuruIndex({ gurus, filters }: GuruIndexProps) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [loading, setLoading] = useState(false);
    const [selectedGuru, setSelectedGuru] = useState<GuruRow | null>(null);

    const applyFilters = (event?: FormEvent) => {
        event?.preventDefault();
        setLoading(true);
        router.get('/guru', { search }, {
            preserveState: true,
            replace: true,
            onFinish: () => setLoading(false),
        });
    };

    const sortBy = (column: string) => {
        const direction = filters.sort === column && filters.direction === 'asc' ? 'desc' : 'asc';
        setLoading(true);
        router.get('/guru', { search, sort: column, direction }, {
            preserveState: true,
            replace: true,
            onFinish: () => setLoading(false),
        });
    };

    const destroy = () => {
        if (!selectedGuru) {
            return;
        }

        router.delete(`/guru/${selectedGuru.id}`, {
            onFinish: () => setSelectedGuru(null),
        });
    };

    const columns: Array<DataTableColumn<GuruRow>> = [
        {
            key: 'kode',
            label: 'Kode',
            sortable: true,
            render: (guru) => <Badge>{guru.kode}</Badge>,
        },
        {
            key: 'nip',
            label: 'NIP',
            sortable: true,
            render: (guru) => guru.nip ?? '-',
        },
        {
            key: 'nama',
            label: 'Nama',
            sortable: true,
            render: (guru) => <span className="font-semibold text-slate-900">{guru.nama}</span>,
        },
        {
            key: 'bidang_studi',
            label: 'Bidang Studi',
            sortable: true,
            render: (guru) => guru.bidang_studi ?? '-',
        },
        {
            key: 'beban_jp',
            label: 'Beban JP',
            sortable: true,
            render: (guru) => formatNumber(guru.beban_jp),
        },
        {
            key: 'actions',
            label: 'Aksi',
            headerClassName: 'w-32 text-right',
            className: 'text-right',
            render: (guru) => (
                <div className="flex justify-end gap-2">
                    <Link href={`/guru/${guru.id}/edit`}>
                        <Button variant="outline" size="icon" title="Edit" aria-label={`Edit guru ${guru.nama}`}>
                            <Edit size={16} />
                        </Button>
                    </Link>
                    <Button variant="danger" size="icon" title="Hapus" aria-label={`Hapus guru ${guru.nama}`} onClick={() => setSelectedGuru(guru)}>
                        <Trash2 size={16} />
                    </Button>
                </div>
            ),
        },
    ];

    return (
        <AppLayout title="Data Guru">
            <Head title="Data Guru" />
            <Card>
                <CardHeader className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <CardTitle>Data Guru</CardTitle>
                        <p className="mt-1 text-sm font-medium text-slate-500">Kelola identitas guru, NIP, bidang studi, dan beban mengajar.</p>
                    </div>
                    <Link href="/guru/create">
                        <Button>
                            <Plus size={17} />
                            Tambah Guru
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
                                placeholder="Cari kode, NIP, nama, atau bidang studi"
                                onChange={(event) => setSearch(event.target.value)}
                            />
                        </div>
                        <Button variant="outline" type="submit" disabled={loading}>
                            {loading ? <Loader2 size={16} className="animate-spin" /> : null}
                            Cari
                        </Button>
                        {filters.search ? (
                            <Button variant="ghost" type="button" onClick={() => router.get('/guru')}>
                                Reset
                            </Button>
                        ) : null}
                    </form>

                    <DataTable
                        data={gurus.data}
                        columns={columns}
                        loading={loading}
                        sort={filters.sort}
                        direction={filters.direction}
                        onSort={sortBy}
                        emptyTitle="Belum ada data guru"
                        emptyDescription="Guru yang cocok dengan filter akan tampil di sini."
                    />

                    <div className="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="text-sm font-medium text-slate-500">
                            Menampilkan {gurus.from ?? 0}-{gurus.to ?? 0} dari {gurus.total} guru
                        </div>
                        <Pagination links={gurus.links} />
                    </div>
                </CardContent>
            </Card>

            <ConfirmDeleteDialog
                open={Boolean(selectedGuru)}
                message={`Hapus guru ${selectedGuru?.nama ?? ''}? Data yang masih punya jadwal atau absensi akan ditolak oleh sistem.`}
                onClose={() => setSelectedGuru(null)}
                onConfirm={destroy}
            />
        </AppLayout>
    );
}
