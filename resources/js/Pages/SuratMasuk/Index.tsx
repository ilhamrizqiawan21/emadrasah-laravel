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
import { Select } from '@/Components/ui/select';
import { formatDate, formatStatus } from '@/lib/format';
import { Paginated } from '@/types';

interface SuratMasukRow {
    id: number;
    nomor_agenda: string;
    asal_surat: string;
    nomor_surat: string | null;
    perihal: string;
    tanggal_terima: string | null;
    tanggal_surat: string | null;
    status: 'diterima' | 'diproses' | 'selesai';
    file_scan_url: string | null;
}

interface SuratMasukIndexProps {
    surat: Paginated<SuratMasukRow>;
    filters: {
        search?: string;
        status?: string;
        sort?: string;
        direction?: string;
    };
}

const statusVariants = {
    diterima: 'default',
    diproses: 'warning',
    selesai: 'success',
} as const;

export default function SuratMasukIndex({ surat, filters }: SuratMasukIndexProps) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState(filters.status ?? '');
    const [loading, setLoading] = useState(false);
    const [selectedIds, setSelectedIds] = useState<number[]>([]);
    const [deleteTarget, setDeleteTarget] = useState<SuratMasukRow | null>(null);
    const [bulkOpen, setBulkOpen] = useState(false);

    const query = { search, status };
    const hasFilters = Boolean(filters.search || filters.status || filters.sort);

    const applyFilters = (event?: FormEvent) => {
        event?.preventDefault();
        setLoading(true);
        router.get('/surat-masuk', query, {
            preserveState: true,
            replace: true,
            onFinish: () => setLoading(false),
        });
    };

    const sortBy = (column: string) => {
        const direction = filters.sort === column && filters.direction === 'asc' ? 'desc' : 'asc';
        setLoading(true);
        router.get('/surat-masuk', { ...query, sort: column, direction }, {
            preserveState: true,
            replace: true,
            onFinish: () => setLoading(false),
        });
    };

    const toggleAll = (checked: boolean) => {
        setSelectedIds(checked ? surat.data.map((item) => item.id) : []);
    };

    const toggleOne = (id: number, checked: boolean) => {
        setSelectedIds((current) => checked ? [...current, id] : current.filter((item) => item !== id));
    };

    const destroy = () => {
        if (!deleteTarget) {
            return;
        }

        router.delete(`/surat-masuk/${deleteTarget.id}`, {
            onFinish: () => setDeleteTarget(null),
        });
    };

    const bulkDestroy = () => {
        router.delete('/surat-masuk/bulk-destroy', {
            data: { ids: selectedIds },
            onFinish: () => {
                setBulkOpen(false);
                setSelectedIds([]);
            },
        });
    };

    const columns: Array<DataTableColumn<SuratMasukRow>> = [
        {
            key: 'select',
            label: '',
            headerClassName: 'w-12',
            render: (item) => (
                <Checkbox
                    checked={selectedIds.includes(item.id)}
                    onChange={(event) => toggleOne(item.id, event.target.checked)}
                    aria-label={`Pilih surat masuk ${item.nomor_agenda}`}
                />
            ),
        },
        {
            key: 'agenda',
            label: 'Nomor Agenda',
            sortable: true,
            render: (item) => <Badge>{item.nomor_agenda}</Badge>,
        },
        {
            key: 'asal',
            label: 'Asal Surat',
            sortable: true,
            render: (item) => (
                <div>
                    <div className="font-semibold text-slate-900">{item.asal_surat}</div>
                    <div className="mt-1 text-xs font-medium text-slate-500">{item.nomor_surat ?? '-'}</div>
                </div>
            ),
        },
        {
            key: 'perihal',
            label: 'Perihal',
            render: (item) => <span className="font-medium text-slate-700">{item.perihal}</span>,
        },
        {
            key: 'tanggal',
            label: 'Tgl Terima',
            sortable: true,
            render: (item) => formatDate(item.tanggal_terima),
        },
        {
            key: 'status',
            label: 'Status',
            sortable: true,
            render: (item) => <Badge variant={statusVariants[item.status]}>{formatStatus(item.status)}</Badge>,
        },
        {
            key: 'file',
            label: 'File',
            headerClassName: 'w-20 text-center',
            className: 'text-center',
            render: (item) => item.file_scan_url ? (
                <a href={item.file_scan_url} target="_blank" rel="noreferrer">
                    <Button variant="outline" size="icon" title="Lihat file" aria-label={`Lihat file ${item.nomor_agenda}`}>
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
                    <a href={`/surat-masuk/${item.id}`}>
                        <Button variant="outline" size="icon" title="Detail" aria-label={`Detail surat masuk ${item.nomor_agenda}`}>
                            <Eye size={16} />
                        </Button>
                    </a>
                    <Link href={`/surat-masuk/${item.id}/edit`}>
                        <Button variant="outline" size="icon" title="Edit" aria-label={`Edit surat masuk ${item.nomor_agenda}`}>
                            <Edit size={16} />
                        </Button>
                    </Link>
                    <Button variant="danger" size="icon" title="Hapus" aria-label={`Hapus surat masuk ${item.nomor_agenda}`} onClick={() => setDeleteTarget(item)}>
                        <Trash2 size={16} />
                    </Button>
                </div>
            ),
        },
    ];

    return (
        <AppLayout title="Surat Masuk">
            <Head title="Surat Masuk" />
            <Card>
                <CardHeader className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <CardTitle>Surat Masuk</CardTitle>
                        <p className="mt-1 text-sm font-medium text-slate-500">Kelola agenda, disposisi, dan arsip surat masuk.</p>
                    </div>
                    <Link href="/surat-masuk/create">
                        <Button>
                            <Plus size={17} />
                            Tambah Surat
                        </Button>
                    </Link>
                </CardHeader>
                <CardContent>
                    <form className="mb-4 grid gap-2 lg:grid-cols-[minmax(220px,1fr)_180px_auto_auto]" onSubmit={applyFilters}>
                        <div className="flex min-w-0 items-center gap-2">
                            <Search size={18} className="shrink-0 text-slate-400" />
                            <Input type="search" value={search} placeholder="Cari agenda, asal, nomor, atau perihal" onChange={(event) => setSearch(event.target.value)} />
                        </div>
                        <Select value={status} onChange={(event) => setStatus(event.target.value)} aria-label="Filter status surat masuk">
                            <option value="">Semua Status</option>
                            <option value="diterima">Diterima</option>
                            <option value="diproses">Diproses</option>
                            <option value="selesai">Selesai</option>
                        </Select>
                        <Button variant="outline" type="submit" disabled={loading}>
                            {loading ? <Loader2 size={16} className="animate-spin" /> : null}
                            Filter
                        </Button>
                        {hasFilters ? (
                            <Button variant="ghost" type="button" onClick={() => router.get('/surat-masuk')}>
                                Reset
                            </Button>
                        ) : null}
                    </form>

                    <div className="mb-3 flex flex-wrap items-center justify-between gap-2">
                        <div className="flex items-center gap-2 text-sm font-semibold text-slate-500">
                            <Checkbox checked={selectedIds.length === surat.data.length && surat.data.length > 0} onChange={(event) => toggleAll(event.target.checked)} aria-label="Pilih semua surat masuk" />
                            {surat.total} surat masuk
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
                        emptyTitle="Belum ada data surat masuk"
                        emptyDescription="Surat masuk yang cocok dengan filter akan tampil di sini."
                    />

                    <div className="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="text-sm font-medium text-slate-500">
                            Menampilkan {surat.from ?? 0}-{surat.to ?? 0} dari {surat.total} surat
                        </div>
                        <Pagination links={surat.links} />
                    </div>
                </CardContent>
            </Card>

            <ConfirmDeleteDialog open={Boolean(deleteTarget)} message={`Hapus surat masuk ${deleteTarget?.nomor_agenda ?? ''}?`} onClose={() => setDeleteTarget(null)} onConfirm={destroy} />
            <ConfirmDeleteDialog open={bulkOpen} message={`Hapus ${selectedIds.length} surat masuk terpilih?`} onClose={() => setBulkOpen(false)} onConfirm={bulkDestroy} />
        </AppLayout>
    );
}
