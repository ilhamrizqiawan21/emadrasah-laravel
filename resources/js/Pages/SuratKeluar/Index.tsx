import { Head, Link, router } from '@inertiajs/react';
import { Edit, Eye, FileText, Loader2, Plus, Search, Trash2 } from 'lucide-react';
import { FormEvent, useState } from 'react';
import { ConfirmDeleteDialog } from '@/Components/ConfirmDeleteDialog';
import { DataTable, DataTableColumn } from '@/Components/DataTable';
import AppLayout from '@/Layouts/AppLayout';
import { Pagination } from '@/Components/Pagination';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Checkbox } from '@/Components/ui/checkbox';
import { Input } from '@/Components/ui/input';
import { formatDate } from '@/lib/format';
import { Paginated } from '@/types';

interface SuratKeluarRow {
    id: number;
    nomor_surat: string;
    tujuan: string;
    perihal: string;
    tanggal_kirim: string | null;
    lampiran: string | null;
    file_draft_url: string | null;
}

interface SuratKeluarIndexProps {
    surat: Paginated<SuratKeluarRow>;
    filters: {
        search?: string;
        sort?: string;
        direction?: string;
    };
}

export default function SuratKeluarIndex({ surat, filters }: SuratKeluarIndexProps) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [loading, setLoading] = useState(false);
    const [selectedIds, setSelectedIds] = useState<number[]>([]);
    const [deleteTarget, setDeleteTarget] = useState<SuratKeluarRow | null>(null);
    const [bulkOpen, setBulkOpen] = useState(false);

    const applyFilters = (event?: FormEvent) => {
        event?.preventDefault();
        setLoading(true);
        router.get('/surat-keluar', { search }, {
            preserveState: true,
            replace: true,
            onFinish: () => setLoading(false),
        });
    };

    const sortBy = (column: string) => {
        const direction = filters.sort === column && filters.direction === 'asc' ? 'desc' : 'asc';
        setLoading(true);
        router.get('/surat-keluar', { search, sort: column, direction }, {
            preserveState: true,
            replace: true,
            onFinish: () => setLoading(false),
        });
    };

    const toggleAll = (checked: boolean) => setSelectedIds(checked ? surat.data.map((item) => item.id) : []);
    const toggleOne = (id: number, checked: boolean) => setSelectedIds((current) => checked ? [...current, id] : current.filter((item) => item !== id));

    const destroy = () => {
        if (!deleteTarget) {
            return;
        }

        router.delete(`/surat-keluar/${deleteTarget.id}`, {
            onFinish: () => setDeleteTarget(null),
        });
    };

    const bulkDestroy = () => {
        router.delete('/surat-keluar/bulk-destroy', {
            data: { ids: selectedIds },
            onFinish: () => {
                setBulkOpen(false);
                setSelectedIds([]);
            },
        });
    };

    const columns: Array<DataTableColumn<SuratKeluarRow>> = [
        {
            key: 'select',
            label: '',
            headerClassName: 'w-12',
            render: (item) => (
                <Checkbox checked={selectedIds.includes(item.id)} onChange={(event) => toggleOne(item.id, event.target.checked)} aria-label={`Pilih surat keluar ${item.nomor_surat}`} />
            ),
        },
        {
            key: 'nomor',
            label: 'Nomor Surat',
            sortable: true,
            render: (item) => <Badge variant="info">{item.nomor_surat}</Badge>,
        },
        {
            key: 'tujuan',
            label: 'Tujuan',
            sortable: true,
            render: (item) => <span className="font-semibold text-slate-900">{item.tujuan}</span>,
        },
        {
            key: 'perihal',
            label: 'Perihal',
            render: (item) => <span className="font-medium text-slate-700">{item.perihal}</span>,
        },
        {
            key: 'tanggal',
            label: 'Tgl Kirim',
            sortable: true,
            render: (item) => formatDate(item.tanggal_kirim),
        },
        {
            key: 'lampiran',
            label: 'Lampiran',
            render: (item) => item.lampiran ?? '-',
        },
        {
            key: 'file',
            label: 'File',
            headerClassName: 'w-20 text-center',
            className: 'text-center',
            render: (item) => item.file_draft_url ? (
                <a href={item.file_draft_url} target="_blank" rel="noreferrer">
                    <Button variant="outline" size="icon" title="Lihat draft" aria-label={`Lihat draft ${item.nomor_surat}`}>
                        <FileText size={16} />
                    </Button>
                </a>
            ) : '-',
        },
        {
            key: 'actions',
            label: 'Aksi',
            headerClassName: 'w-36 text-right',
            className: 'text-right',
            render: (item) => (
                <div className="flex justify-end gap-2">
                    <a href={`/surat-keluar/${item.id}`}>
                        <Button variant="outline" size="icon" title="Detail" aria-label={`Detail surat keluar ${item.nomor_surat}`}>
                            <Eye size={16} />
                        </Button>
                    </a>
                    <Link href={`/surat-keluar/${item.id}/edit`}>
                        <Button variant="outline" size="icon" title="Edit" aria-label={`Edit surat keluar ${item.nomor_surat}`}>
                            <Edit size={16} />
                        </Button>
                    </Link>
                    <Button variant="danger" size="icon" title="Hapus" aria-label={`Hapus surat keluar ${item.nomor_surat}`} onClick={() => setDeleteTarget(item)}>
                        <Trash2 size={16} />
                    </Button>
                </div>
            ),
        },
    ];

    return (
        <AppLayout title="Surat Keluar">
            <Head title="Surat Keluar" />
            <Card>
                <CardHeader className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <CardTitle>Surat Keluar</CardTitle>
                        <p className="mt-1 text-sm font-medium text-slate-500">Kelola penomoran, tujuan, dan draft surat keluar.</p>
                    </div>
                    <Link href="/surat-keluar/create">
                        <Button>
                            <Plus size={17} />
                            Tambah Surat
                        </Button>
                    </Link>
                </CardHeader>
                <CardContent>
                    <form className="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center" onSubmit={applyFilters}>
                        <div className="flex min-w-0 items-center gap-2 sm:min-w-96">
                            <Search size={18} className="shrink-0 text-slate-400" />
                            <Input type="search" value={search} placeholder="Cari nomor, tujuan, atau perihal" onChange={(event) => setSearch(event.target.value)} />
                        </div>
                        <Button variant="outline" type="submit" disabled={loading}>
                            {loading ? <Loader2 size={16} className="animate-spin" /> : null}
                            Filter
                        </Button>
                        {filters.search || filters.sort ? (
                            <Button variant="ghost" type="button" onClick={() => router.get('/surat-keluar')}>
                                Reset
                            </Button>
                        ) : null}
                    </form>

                    <div className="mb-3 flex flex-wrap items-center justify-between gap-2">
                        <div className="flex items-center gap-2 text-sm font-semibold text-slate-500">
                            <Checkbox checked={selectedIds.length === surat.data.length && surat.data.length > 0} onChange={(event) => toggleAll(event.target.checked)} aria-label="Pilih semua surat keluar" />
                            {surat.total} surat keluar
                        </div>
                        <Button variant="danger" size="sm" disabled={selectedIds.length === 0} onClick={() => setBulkOpen(true)}>
                            <Trash2 size={16} />
                            Hapus Terpilih
                        </Button>
                    </div>

                    <DataTable
                        data={surat.data}
                        columns={columns}
                        loading={loading}
                        sort={filters.sort}
                        direction={filters.direction}
                        onSort={sortBy}
                        emptyTitle="Belum ada data surat keluar"
                        emptyDescription="Surat keluar yang cocok dengan filter akan tampil di sini."
                    />

                    <div className="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="text-sm font-medium text-slate-500">
                            Menampilkan {surat.from ?? 0}-{surat.to ?? 0} dari {surat.total} surat
                        </div>
                        <Pagination links={surat.links} />
                    </div>
                </CardContent>
            </Card>

            <ConfirmDeleteDialog open={Boolean(deleteTarget)} message={`Hapus surat keluar ${deleteTarget?.nomor_surat ?? ''}?`} onClose={() => setDeleteTarget(null)} onConfirm={destroy} />
            <ConfirmDeleteDialog open={bulkOpen} message={`Hapus ${selectedIds.length} surat keluar terpilih?`} onClose={() => setBulkOpen(false)} onConfirm={bulkDestroy} />
        </AppLayout>
    );
}
