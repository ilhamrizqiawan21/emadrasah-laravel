import { Head, Link, router } from '@inertiajs/react';
import { Edit, History, Loader2, Plus, Search, Tags, Trash2, Wrench } from 'lucide-react';
import { useState } from 'react';
import { ConfirmDeleteDialog } from '@/Components/ConfirmDeleteDialog';
import { DataTable, DataTableColumn } from '@/Components/DataTable';
import AppLayout from '@/Layouts/AppLayout';
import { Pagination } from '@/Components/Pagination';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Select } from '@/Components/ui/select';
import { formatNumber, formatStatus } from '@/lib/format';
import { useIndexFilters } from '@/lib/query';
import { Paginated } from '@/types';

type KondisiSarana = 'baik' | 'rusak_ringan' | 'rusak_berat' | 'hilang';

interface KategoriOption {
    id: number;
    nama_kategori: string;
}

interface SaranaRow {
    id: number;
    kode_sarana: string;
    nama_sarana: string;
    kategori_id: number;
    kategori: KategoriOption | null;
    jumlah: number;
    stok_tersedia: number;
    kondisi: KondisiSarana;
    lokasi_ruang: string | null;
    tahun_pengadaan: string | null;
    foto_url: string | null;
}

interface SaranaIndexProps {
    sarana: Paginated<SaranaRow>;
    kategori: KategoriOption[];
    filters: {
        search?: string;
        kategori_id?: string;
        kondisi?: string;
        sort?: string;
        direction?: string;
    };
}

const kondisiVariants: Record<KondisiSarana, 'success' | 'warning' | 'danger' | 'default'> = {
    baik: 'success',
    rusak_ringan: 'warning',
    rusak_berat: 'danger',
    hilang: 'default',
};

export default function SaranaIndex({ sarana, kategori, filters }: SaranaIndexProps) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [kategoriId, setKategoriId] = useState(filters.kategori_id ?? '');
    const [kondisi, setKondisi] = useState(filters.kondisi ?? '');
    const [deleteTarget, setDeleteTarget] = useState<SaranaRow | null>(null);

    const query = { search, kategori_id: kategoriId, kondisi };
    const { loading, reset, visit } = useIndexFilters({ url: '/sarana', values: query, debounceKeys: ['search'] });

    const sortBy = (column: string) => {
        const direction = filters.sort === column && filters.direction === 'asc' ? 'desc' : 'asc';
        visit(undefined, { sort: column, direction });
    };

    const destroy = () => {
        if (!deleteTarget) {
            return;
        }

        router.delete(`/sarana/${deleteTarget.id}`, {
            onFinish: () => setDeleteTarget(null),
        });
    };

    const hasFilters = Boolean(filters.search || filters.kategori_id || filters.kondisi || filters.sort);

    const columns: Array<DataTableColumn<SaranaRow>> = [
        {
            key: 'kode',
            label: 'Kode',
            sortable: true,
            render: (item) => <Badge variant="info">{item.kode_sarana}</Badge>,
        },
        {
            key: 'nama',
            label: 'Nama Sarana',
            sortable: true,
            render: (item) => (
                <div className="min-w-48">
                    <div className="font-semibold text-slate-900">{item.nama_sarana}</div>
                    <div className="mt-1 text-xs font-medium text-slate-500">{item.tahun_pengadaan ?? 'Tahun belum diisi'}</div>
                </div>
            ),
        },
        {
            key: 'kategori',
            label: 'Kategori',
            sortable: true,
            render: (item) => item.kategori?.nama_kategori ?? '-',
        },
        {
            key: 'jumlah',
            label: 'Jumlah',
            sortable: true,
            render: (item) => (
                <div className="font-semibold text-slate-900">
                    {formatNumber(item.stok_tersedia)} / {formatNumber(item.jumlah)}
                </div>
            ),
        },
        {
            key: 'kondisi',
            label: 'Kondisi',
            sortable: true,
            render: (item) => <Badge variant={kondisiVariants[item.kondisi]}>{formatStatus(item.kondisi)}</Badge>,
        },
        {
            key: 'lokasi',
            label: 'Lokasi',
            sortable: true,
            render: (item) => item.lokasi_ruang ?? '-',
        },
        {
            key: 'actions',
            label: 'Aksi',
            headerClassName: 'w-48 text-right',
            className: 'text-right',
            render: (item) => (
                <div className="flex justify-end gap-2">
                    <Link href={`/sarana/${item.id}/edit`}>
                        <Button variant="outline" size="icon" title="Edit" aria-label={`Edit sarana ${item.nama_sarana}`}>
                            <Edit size={16} />
                        </Button>
                    </Link>
                    <Link href={`/sarana/${item.id}/peminjaman`}>
                        <Button variant="outline" size="icon" title="Peminjaman" aria-label={`Peminjaman sarana ${item.nama_sarana}`}>
                            <History size={16} />
                        </Button>
                    </Link>
                    <Link href={`/sarana/${item.id}/pemeliharaan`}>
                        <Button variant="outline" size="icon" title="Pemeliharaan" aria-label={`Pemeliharaan sarana ${item.nama_sarana}`}>
                            <Wrench size={16} />
                        </Button>
                    </Link>
                    <Button variant="danger" size="icon" title="Hapus" aria-label={`Hapus sarana ${item.nama_sarana}`} onClick={() => setDeleteTarget(item)}>
                        <Trash2 size={16} />
                    </Button>
                </div>
            ),
        },
    ];

    return (
        <AppLayout title="Sarana Prasarana">
            <Head title="Sarana Prasarana" />
            <Card>
                <CardHeader className="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <CardTitle>Sarana Prasarana</CardTitle>
                        <p className="mt-1 text-sm font-medium text-slate-500">Kelola inventaris, kondisi, lokasi, dan stok tersedia.</p>
                    </div>
                    <div className="flex flex-col gap-2 sm:flex-row">
                        <Link href="/kategori-sarana">
                            <Button variant="outline">
                                <Tags size={17} />
                                Kelola Kategori
                            </Button>
                        </Link>
                        <Link href="/sarana/create">
                            <Button>
                                <Plus size={17} />
                                Tambah Sarana
                            </Button>
                        </Link>
                    </div>
                </CardHeader>
                <CardContent>
                    <form className="mb-4 grid gap-2 xl:grid-cols-[minmax(220px,1fr)_220px_200px_auto_auto]" onSubmit={visit}>
                        <div className="flex min-w-0 items-center gap-2">
                            <Search size={18} className="shrink-0 text-slate-400" />
                            <Input type="search" value={search} placeholder="Cari kode, nama, kategori, atau lokasi" onChange={(event) => setSearch(event.target.value)} />
                        </div>
                        <Select value={kategoriId} onChange={(event) => setKategoriId(event.target.value)} aria-label="Filter kategori">
                            <option value="">Semua Kategori</option>
                            {kategori.map((item) => <option key={item.id} value={item.id}>{item.nama_kategori}</option>)}
                        </Select>
                        <Select value={kondisi} onChange={(event) => setKondisi(event.target.value)} aria-label="Filter kondisi">
                            <option value="">Semua Kondisi</option>
                            <option value="baik">Baik</option>
                            <option value="rusak_ringan">Rusak Ringan</option>
                            <option value="rusak_berat">Rusak Berat</option>
                            <option value="hilang">Hilang</option>
                        </Select>
                        <Button variant="outline" type="submit" disabled={loading}>
                            {loading ? <Loader2 size={16} className="animate-spin" /> : null}
                            Filter
                        </Button>
                        {hasFilters ? <Button variant="ghost" type="button" onClick={reset}>Reset</Button> : null}
                    </form>

                    <DataTable
                        data={sarana.data}
                        columns={columns}
                        loading={loading}
                        sort={filters.sort}
                        direction={filters.direction}
                        onSort={sortBy}
                        emptyTitle="Belum ada data sarana"
                        emptyDescription="Sarana yang cocok dengan filter akan tampil di sini."
                    />

                    <div className="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="text-sm font-medium text-slate-500">
                            Menampilkan {sarana.from ?? 0}-{sarana.to ?? 0} dari {sarana.total} sarana
                        </div>
                        <Pagination links={sarana.links} />
                    </div>
                </CardContent>
            </Card>

            <ConfirmDeleteDialog
                open={Boolean(deleteTarget)}
                message={`Hapus sarana ${deleteTarget?.nama_sarana ?? ''}? Riwayat terkait tetap mengikuti aturan data sistem.`}
                onClose={() => setDeleteTarget(null)}
                onConfirm={destroy}
            />
        </AppLayout>
    );
}
