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
import { Select } from '@/Components/ui/select';
import { formatDate } from '@/lib/format';
import { Paginated } from '@/types';

interface Option {
    id: number;
    nama_kelas?: string;
    kode?: string;
    is_aktif?: boolean;
}

interface SiswaRow {
    id: number;
    nis: string;
    nisn: string | null;
    nama_lengkap: string;
    jenis_kelamin: 'L' | 'P' | null;
    tempat_lahir: string | null;
    tanggal_lahir: string | null;
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

interface SiswaIndexProps {
    siswa: Paginated<SiswaRow>;
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

const statuses = ['Aktif', 'Lulus', 'Pindah', 'Keluar'];

function statusVariant(status?: string | null) {
    if (status === 'Aktif') {
        return 'success';
    }

    if (status === 'Keluar') {
        return 'danger';
    }

    return 'warning';
}

export default function SiswaIndex({ siswa, kelas, tahunPelajaran, filters }: SiswaIndexProps) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState(filters.status ?? '');
    const [kelasId, setKelasId] = useState(filters.kelas_id ?? '');
    const [tahunPelajaranId, setTahunPelajaranId] = useState(filters.tahun_pelajaran_id ?? '');
    const [loading, setLoading] = useState(false);
    const [selectedSiswa, setSelectedSiswa] = useState<SiswaRow | null>(null);

    const query = {
        search,
        status,
        kelas_id: kelasId,
        tahun_pelajaran_id: tahunPelajaranId,
    };

    const applyFilters = (event?: FormEvent) => {
        event?.preventDefault();
        setLoading(true);
        router.get('/siswa', query, {
            preserveState: true,
            replace: true,
            onFinish: () => setLoading(false),
        });
    };

    const sortBy = (column: string) => {
        const direction = filters.sort === column && filters.direction === 'asc' ? 'desc' : 'asc';
        setLoading(true);
        router.get('/siswa', { ...query, sort: column, direction }, {
            preserveState: true,
            replace: true,
            onFinish: () => setLoading(false),
        });
    };

    const destroy = () => {
        if (!selectedSiswa) {
            return;
        }

        router.delete(`/siswa/${selectedSiswa.id}`, {
            onFinish: () => setSelectedSiswa(null),
        });
    };

    const hasFilters = Boolean(filters.search || filters.status || filters.kelas_id || filters.tahun_pelajaran_id || filters.sort);

    const columns: Array<DataTableColumn<SiswaRow>> = [
        {
            key: 'nis',
            label: 'NIS / NISN',
            sortable: true,
            render: (item) => (
                <div>
                    <div className="font-semibold text-blue-700">{item.nis}</div>
                    <div className="mt-1 text-xs font-medium text-slate-500">{item.nisn ?? '-'}</div>
                </div>
            ),
        },
        {
            key: 'nama',
            label: 'Nama Lengkap',
            sortable: true,
            render: (item) => (
                <div>
                    <div className="font-semibold text-slate-900">{item.nama_lengkap}</div>
                    <div className="mt-1 text-xs font-medium text-slate-500">
                        {[item.tempat_lahir, formatDate(item.tanggal_lahir)].filter((value) => value && value !== '-').join(', ') || '-'}
                    </div>
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
            key: 'jenis_kelamin',
            label: 'L/P',
            headerClassName: 'w-20',
            render: (item) => <Badge variant={item.jenis_kelamin === 'P' ? 'danger' : 'info'}>{item.jenis_kelamin ?? '-'}</Badge>,
        },
        {
            key: 'status',
            label: 'Status',
            sortable: true,
            render: (item) => <Badge variant={statusVariant(item.status)}>{item.status ?? 'Aktif'}</Badge>,
        },
        {
            key: 'tahun_pelajaran',
            label: 'Tahun',
            render: (item) => item.tahun_pelajaran?.kode ?? '-',
        },
        {
            key: 'actions',
            label: 'Aksi',
            headerClassName: 'w-32 text-right',
            className: 'text-right',
            render: (item) => (
                <div className="flex justify-end gap-2">
                    <Link href={`/siswa/${item.id}/edit`}>
                        <Button variant="outline" size="icon" title="Edit" aria-label={`Edit siswa ${item.nama_lengkap}`}>
                            <Edit size={16} />
                        </Button>
                    </Link>
                    <Button variant="danger" size="icon" title="Hapus" aria-label={`Hapus siswa ${item.nama_lengkap}`} onClick={() => setSelectedSiswa(item)}>
                        <Trash2 size={16} />
                    </Button>
                </div>
            ),
        },
    ];

    return (
        <AppLayout title="Data Siswa">
            <Head title="Data Siswa" />
            <Card>
                <CardHeader className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <CardTitle>Data Siswa</CardTitle>
                        <p className="mt-1 text-sm font-medium text-slate-500">Kelola data seluruh siswa MTs Al-Ihsan Batujajar.</p>
                    </div>
                    <Link href="/siswa/create">
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
                                placeholder="Cari NIS, NISN, atau nama"
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
                            <Button variant="ghost" type="button" onClick={() => router.get('/siswa')}>
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
                        emptyDescription="Ubah filter atau tambahkan siswa baru."
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
                message={`Hapus siswa ${selectedSiswa?.nama_lengkap ?? ''}? Data akademik terkait perlu diperiksa sebelum penghapusan.`}
                onClose={() => setSelectedSiswa(null)}
                onConfirm={destroy}
            />
        </AppLayout>
    );
}
