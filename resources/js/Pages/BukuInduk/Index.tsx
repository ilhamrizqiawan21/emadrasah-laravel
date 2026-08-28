import { Head, Link, router } from '@inertiajs/react';
import { Edit, Eye, Loader2, Plus, Search, Trash2 } from 'lucide-react';
import { FormEvent, useState } from 'react';
import { ConfirmDeleteDialog } from '@/Components/ConfirmDeleteDialog';
import { DataTable, DataTableColumn } from '@/Components/DataTable';
import AppLayout from '@/Layouts/AppLayout';
import { Pagination } from '@/Components/Pagination';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Select } from '@/Components/ui/select';
import { Paginated } from '@/types';

interface Option {
    id: number;
    nama_kelas?: string;
    kode?: string;
}

interface BukuIndukRow {
    id: number;
    no_urut: number | null;
    nis: string;
    nisn: string | null;
    nik: string | null;
    nama_lengkap: string;
    status: string | null;
    kelas: {
        id: number;
        nama_kelas: string;
    } | null;
    tahun_pelajaran: {
        id: number;
        kode: string;
    } | null;
}

interface BukuIndukIndexProps {
    siswa: Paginated<BukuIndukRow>;
    kelas: Option[];
    tahunPelajaran: Option[];
    filters: {
        search?: string;
        status?: string;
        kelas_id?: string;
        tahun_pelajaran_id?: string;
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

export default function BukuIndukIndex({ siswa, kelas, tahunPelajaran, filters }: BukuIndukIndexProps) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState(filters.status ?? '');
    const [kelasId, setKelasId] = useState(filters.kelas_id ?? '');
    const [tahunPelajaranId, setTahunPelajaranId] = useState(filters.tahun_pelajaran_id ?? '');
    const [loading, setLoading] = useState(false);
    const [selectedSiswa, setSelectedSiswa] = useState<BukuIndukRow | null>(null);

    const query = {
        search,
        status,
        kelas_id: kelasId,
        tahun_pelajaran_id: tahunPelajaranId,
    };

    const applyFilters = (event?: FormEvent) => {
        event?.preventDefault();
        setLoading(true);
        router.get('/buku-induk', query, {
            preserveState: true,
            replace: true,
            onFinish: () => setLoading(false),
        });
    };

    const sortBy = (column: string) => {
        const direction = filters.sort === column && filters.direction === 'asc' ? 'desc' : 'asc';
        setLoading(true);
        router.get('/buku-induk', { ...query, sort: column, direction }, {
            preserveState: true,
            replace: true,
            onFinish: () => setLoading(false),
        });
    };

    const destroy = () => {
        if (!selectedSiswa) {
            return;
        }

        router.delete(`/buku-induk/${selectedSiswa.id}`, {
            onFinish: () => setSelectedSiswa(null),
        });
    };

    const hasFilters = Boolean(filters.search || filters.status || filters.kelas_id || filters.tahun_pelajaran_id || filters.sort);

    const columns: Array<DataTableColumn<BukuIndukRow>> = [
        {
            key: 'no_urut',
            label: 'No. Urut',
            sortable: true,
            render: (item) => item.no_urut ?? '-',
        },
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
            label: 'Nama Lengkap',
            sortable: true,
            render: (item) => (
                <div>
                    <div className="font-semibold text-slate-900">{item.nama_lengkap}</div>
                    <div className="mt-1 text-xs font-medium text-slate-500">NIK: {item.nik ?? '-'}</div>
                </div>
            ),
        },
        {
            key: 'kelas',
            label: 'Kelas',
            sortable: true,
            render: (item) => <Badge>{item.kelas?.nama_kelas ?? '-'}</Badge>,
        },
        {
            key: 'tahun_pelajaran',
            label: 'Tahun Masuk',
            render: (item) => item.tahun_pelajaran?.kode ?? '-',
        },
        {
            key: 'status',
            label: 'Status',
            sortable: true,
            render: (item) => <Badge variant={statusVariant(item.status)}>{item.status ?? 'Aktif'}</Badge>,
        },
        {
            key: 'actions',
            label: 'Aksi',
            headerClassName: 'w-40 text-right',
            className: 'text-right',
            render: (item) => (
                <div className="flex justify-end gap-2">
                    <Link href={`/buku-induk/${item.id}`}>
                        <Button variant="outline" size="icon" title="Lihat profil" aria-label={`Lihat profil ${item.nama_lengkap}`}>
                            <Eye size={16} />
                        </Button>
                    </Link>
                    <Link href={`/buku-induk/${item.id}/edit`}>
                        <Button variant="outline" size="icon" title="Edit" aria-label={`Edit buku induk ${item.nama_lengkap}`}>
                            <Edit size={16} />
                        </Button>
                    </Link>
                    <Button variant="danger" size="icon" title="Hapus" aria-label={`Hapus buku induk ${item.nama_lengkap}`} onClick={() => setSelectedSiswa(item)}>
                        <Trash2 size={16} />
                    </Button>
                </div>
            ),
        },
    ];

    return (
        <AppLayout title="Buku Induk">
            <Head title="Buku Induk" />
            <Card>
                <CardHeader className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <CardTitle>Buku Induk Register Siswa</CardTitle>
                        <p className="mt-1 text-sm font-medium text-slate-500">Manajemen data lengkap siswa standar Kurikulum Merdeka.</p>
                    </div>
                    <Link href="/buku-induk/create">
                        <Button>
                            <Plus size={17} />
                            Tambah Siswa
                        </Button>
                    </Link>
                </CardHeader>
                <CardContent>
                    <form className="mb-4 grid gap-2 lg:grid-cols-[minmax(220px,1fr)_180px_180px_220px_auto_auto]" onSubmit={applyFilters}>
                        <div className="flex min-w-0 items-center gap-2">
                            <Search size={18} className="shrink-0 text-slate-400" />
                            <Input
                                type="search"
                                value={search}
                                placeholder="Cari nama, NIS, NISN, atau NIK"
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
                        <Select value={tahunPelajaranId} onChange={(event) => setTahunPelajaranId(event.target.value)} aria-label="Filter tahun pelajaran">
                            <option value="">Semua Tahun</option>
                            {tahunPelajaran.map((item) => (
                                <option key={item.id} value={item.id}>
                                    {item.kode}
                                </option>
                            ))}
                        </Select>
                        <Button variant="outline" type="submit" disabled={loading}>
                            {loading ? <Loader2 size={16} className="animate-spin" /> : null}
                            Filter
                        </Button>
                        {hasFilters ? (
                            <Button variant="ghost" type="button" onClick={() => router.get('/buku-induk')}>
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
                        emptyTitle="Belum ada data buku induk"
                        emptyDescription="Data siswa yang cocok dengan filter akan tampil di sini."
                    />

                    <div className="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="text-sm font-medium text-slate-500">
                            Menampilkan {siswa.from ?? 0}-{siswa.to ?? 0} dari {siswa.total} siswa
                        </div>
                        <Pagination links={siswa.links} />
                    </div>
                </CardContent>
            </Card>

            <ConfirmDeleteDialog
                open={Boolean(selectedSiswa)}
                message={`Hapus data buku induk ${selectedSiswa?.nama_lengkap ?? ''}? Data siswa akan masuk arsip hapus sementara.`}
                onClose={() => setSelectedSiswa(null)}
                onConfirm={destroy}
            />
        </AppLayout>
    );
}
