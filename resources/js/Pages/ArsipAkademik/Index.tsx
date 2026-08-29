import { Head, router, useForm } from '@inertiajs/react';
import { Download, Loader2, Search, Trash2, Upload } from 'lucide-react';
import { FormEvent, useState } from 'react';
import { ConfirmDeleteDialog } from '@/Components/ConfirmDeleteDialog';
import { DataTable, DataTableColumn } from '@/Components/DataTable';
import { ErrorSummary } from '@/Components/ErrorSummary';
import { FileUploadField } from '@/Components/FileUploadField';
import { FormField } from '@/Components/FormField';
import AppLayout from '@/Layouts/AppLayout';
import { Pagination } from '@/Components/Pagination';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Checkbox } from '@/Components/ui/checkbox';
import { Dialog } from '@/Components/ui/dialog';
import { Input } from '@/Components/ui/input';
import { Select } from '@/Components/ui/select';
import { formatDate } from '@/lib/format';
import { useIndexFilters } from '@/lib/query';
import { Paginated } from '@/types';

type ArsipTipe = 'Leger' | 'RDM' | 'Lainnya';

interface KelasOption {
    id: number;
    nama_kelas: string;
}

interface TahunPelajaranOption {
    id: number;
    kode: string;
    is_aktif?: boolean;
}

interface ArsipAkademikRow {
    id: number;
    nama_arsip: string;
    tipe: ArsipTipe;
    semester: '1' | '2' | number;
    file_name: string;
    file_url: string;
    created_at: string | null;
    kelas: KelasOption | null;
    tahun_pelajaran: Pick<TahunPelajaranOption, 'id' | 'kode'> | null;
}

interface ArsipAkademikIndexProps {
    arsip: Paginated<ArsipAkademikRow>;
    kelas: KelasOption[];
    tahunPelajaran: TahunPelajaranOption[];
    filters: {
        search?: string;
        tipe?: string;
        kelas_id?: string;
        tahun_pelajaran_id?: string;
        semester?: string;
        sort?: string;
        direction?: string;
    };
}

interface ArsipFormData {
    tahun_pelajaran_id: string;
    kelas_id: string;
    semester: string;
    nama_arsip: string;
    file_arsip: File | null;
    tipe: ArsipTipe;
}

const tipeVariants: Record<ArsipTipe, 'info' | 'success' | 'default'> = {
    Leger: 'info',
    RDM: 'success',
    Lainnya: 'default',
};

function activeTahunPelajaran(tahunPelajaran: TahunPelajaranOption[]) {
    return tahunPelajaran.find((item) => item.is_aktif)?.id.toString() ?? tahunPelajaran[0]?.id.toString() ?? '';
}

function semesterLabel(value: string | number) {
    return value.toString() === '1' ? '1 (Ganjil)' : '2 (Genap)';
}

export default function ArsipAkademikIndex({ arsip, kelas, tahunPelajaran, filters }: ArsipAkademikIndexProps) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [tipe, setTipe] = useState(filters.tipe ?? '');
    const [kelasId, setKelasId] = useState(filters.kelas_id ?? '');
    const [tahunPelajaranId, setTahunPelajaranId] = useState(filters.tahun_pelajaran_id ?? '');
    const [semester, setSemester] = useState(filters.semester ?? '');
    const [selectedIds, setSelectedIds] = useState<number[]>([]);
    const [uploadOpen, setUploadOpen] = useState(false);
    const [deleteTarget, setDeleteTarget] = useState<ArsipAkademikRow | null>(null);
    const [bulkOpen, setBulkOpen] = useState(false);

    const form = useForm<ArsipFormData>({
        tahun_pelajaran_id: activeTahunPelajaran(tahunPelajaran),
        kelas_id: kelas[0]?.id.toString() ?? '',
        semester: '1',
        nama_arsip: '',
        file_arsip: null,
        tipe: 'Leger',
    });

    const query = { search, tipe, kelas_id: kelasId, tahun_pelajaran_id: tahunPelajaranId, semester };
    const { loading, reset, visit } = useIndexFilters({ url: '/arsip-akademik', values: query, debounceKeys: ['search'] });

    const sortBy = (column: string) => {
        const direction = filters.sort === column && filters.direction === 'asc' ? 'desc' : 'asc';
        visit(undefined, { sort: column, direction });
    };

    const upload = (event: FormEvent) => {
        event.preventDefault();
        form.post('/arsip-akademik', {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                setUploadOpen(false);
            },
        });
    };

    const toggleAll = (checked: boolean) => setSelectedIds(checked ? arsip.data.map((item) => item.id) : []);
    const toggleOne = (id: number, checked: boolean) => setSelectedIds((current) => checked ? [...current, id] : current.filter((item) => item !== id));

    const destroy = () => {
        if (!deleteTarget) {
            return;
        }

        router.delete(`/arsip-akademik/${deleteTarget.id}`, {
            onFinish: () => setDeleteTarget(null),
        });
    };

    const bulkDestroy = () => {
        router.delete('/arsip-akademik/bulk-destroy', {
            data: { ids: selectedIds },
            onFinish: () => {
                setBulkOpen(false);
                setSelectedIds([]);
            },
        });
    };

    const hasFilters = Boolean(filters.search || filters.tipe || filters.kelas_id || filters.tahun_pelajaran_id || filters.semester || filters.sort);

    const columns: Array<DataTableColumn<ArsipAkademikRow>> = [
        {
            key: 'select',
            label: '',
            headerClassName: 'w-12',
            render: (item) => (
                <Checkbox checked={selectedIds.includes(item.id)} onChange={(event) => toggleOne(item.id, event.target.checked)} aria-label={`Pilih arsip ${item.nama_arsip}`} />
            ),
        },
        {
            key: 'nama',
            label: 'Nama Arsip / File',
            sortable: true,
            render: (item) => (
                <div className="min-w-56">
                    <div className="font-semibold text-slate-900">{item.nama_arsip}</div>
                    <div className="mt-1 max-w-64 truncate text-xs font-medium text-slate-500">{item.file_name}</div>
                </div>
            ),
        },
        {
            key: 'tipe',
            label: 'Tipe',
            sortable: true,
            render: (item) => <Badge variant={tipeVariants[item.tipe]}>{item.tipe}</Badge>,
        },
        {
            key: 'kelas',
            label: 'Kelas / Semester',
            sortable: true,
            render: (item) => (
                <div>
                    <div className="font-semibold text-slate-900">{item.kelas?.nama_kelas ?? '-'}</div>
                    <div className="mt-1 text-xs font-medium text-slate-500">Semester {semesterLabel(item.semester)}</div>
                </div>
            ),
        },
        {
            key: 'tahun',
            label: 'Tahun Pelajaran',
            sortable: true,
            render: (item) => item.tahun_pelajaran ? <Badge>{item.tahun_pelajaran.kode}</Badge> : '-',
        },
        {
            key: 'tanggal',
            label: 'Tanggal Upload',
            sortable: true,
            render: (item) => formatDate(item.created_at),
        },
        {
            key: 'actions',
            label: 'Aksi',
            headerClassName: 'w-28 text-right',
            className: 'text-right',
            render: (item) => (
                <div className="flex justify-end gap-2">
                    <a href={item.file_url} target="_blank" rel="noreferrer">
                        <Button variant="outline" size="icon" title="Download / Lihat" aria-label={`Download arsip ${item.nama_arsip}`}>
                            <Download size={16} />
                        </Button>
                    </a>
                    <Button variant="danger" size="icon" title="Hapus" aria-label={`Hapus arsip ${item.nama_arsip}`} onClick={() => setDeleteTarget(item)}>
                        <Trash2 size={16} />
                    </Button>
                </div>
            ),
        },
    ];

    return (
        <AppLayout title="Arsip Akademik">
            <Head title="Arsip Akademik" />
            <Card>
                <CardHeader className="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <CardTitle>Arsip Akademik</CardTitle>
                        <p className="mt-1 text-sm font-medium text-slate-500">Penyimpanan digital untuk dokumen administrasi TU.</p>
                    </div>
                    <Button onClick={() => setUploadOpen(true)}>
                        <Upload size={17} />
                        Upload Arsip Baru
                    </Button>
                </CardHeader>
                <CardContent>
                    <form className="mb-4 grid gap-2 xl:grid-cols-[minmax(220px,1fr)_160px_160px_190px_160px_auto_auto]" onSubmit={visit}>
                        <div className="flex min-w-0 items-center gap-2">
                            <Search size={18} className="shrink-0 text-slate-400" />
                            <Input type="search" value={search} placeholder="Cari nama arsip" onChange={(event) => setSearch(event.target.value)} />
                        </div>
                        <Select value={tipe} onChange={(event) => setTipe(event.target.value)} aria-label="Filter tipe">
                            <option value="">Semua Tipe</option>
                            <option value="Leger">Leger</option>
                            <option value="RDM">RDM</option>
                            <option value="Lainnya">Lainnya</option>
                        </Select>
                        <Select value={kelasId} onChange={(event) => setKelasId(event.target.value)} aria-label="Filter kelas">
                            <option value="">Semua Kelas</option>
                            {kelas.map((item) => <option key={item.id} value={item.id}>{item.nama_kelas}</option>)}
                        </Select>
                        <Select value={tahunPelajaranId} onChange={(event) => setTahunPelajaranId(event.target.value)} aria-label="Filter tahun pelajaran">
                            <option value="">Semua Tahun</option>
                            {tahunPelajaran.map((item) => <option key={item.id} value={item.id}>{item.kode}</option>)}
                        </Select>
                        <Select value={semester} onChange={(event) => setSemester(event.target.value)} aria-label="Filter semester">
                            <option value="">Semua Semester</option>
                            <option value="1">Semester 1</option>
                            <option value="2">Semester 2</option>
                        </Select>
                        <Button variant="outline" type="submit" disabled={loading}>
                            {loading ? <Loader2 size={16} className="animate-spin" /> : null}
                            Filter
                        </Button>
                        {hasFilters ? <Button variant="ghost" type="button" onClick={reset}>Reset</Button> : null}
                    </form>

                    <div className="mb-3 flex flex-wrap items-center justify-between gap-2">
                        <div className="flex items-center gap-2 text-sm font-semibold text-slate-500">
                            <Checkbox checked={selectedIds.length === arsip.data.length && arsip.data.length > 0} onChange={(event) => toggleAll(event.target.checked)} aria-label="Pilih semua arsip akademik" />
                            {arsip.total} arsip akademik
                        </div>
                        <Button variant="danger" size="sm" disabled={selectedIds.length === 0} onClick={() => setBulkOpen(true)}>
                            <Trash2 size={16} />
                            Hapus Terpilih
                        </Button>
                    </div>

                    <DataTable
                        data={arsip.data}
                        columns={columns}
                        loading={loading}
                        sort={filters.sort}
                        direction={filters.direction}
                        onSort={sortBy}
                        emptyTitle="Belum ada arsip akademik"
                        emptyDescription="Arsip yang cocok dengan filter akan tampil di sini."
                    />

                    <div className="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="text-sm font-medium text-slate-500">
                            Menampilkan {arsip.from ?? 0}-{arsip.to ?? 0} dari {arsip.total} arsip
                        </div>
                        <Pagination links={arsip.links} />
                    </div>
                </CardContent>
            </Card>

            <Dialog open={uploadOpen} title="Upload Arsip Akademik" onClose={() => setUploadOpen(false)}>
                <form className="grid gap-4" onSubmit={upload}>
                    <ErrorSummary errors={form.errors} />
                    <FormField label="Nama Arsip" error={form.errors.nama_arsip}>
                        <Input value={form.data.nama_arsip} required onChange={(event) => form.setData('nama_arsip', event.target.value)} />
                    </FormField>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <FormField label="Tahun Pelajaran" error={form.errors.tahun_pelajaran_id}>
                            <Select value={form.data.tahun_pelajaran_id} required onChange={(event) => form.setData('tahun_pelajaran_id', event.target.value)}>
                                <option value="">Pilih Tahun</option>
                                {tahunPelajaran.map((item) => <option key={item.id} value={item.id}>{item.kode}</option>)}
                            </Select>
                        </FormField>
                        <FormField label="Kelas" error={form.errors.kelas_id}>
                            <Select value={form.data.kelas_id} required onChange={(event) => form.setData('kelas_id', event.target.value)}>
                                <option value="">Pilih Kelas</option>
                                {kelas.map((item) => <option key={item.id} value={item.id}>{item.nama_kelas}</option>)}
                            </Select>
                        </FormField>
                    </div>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <FormField label="Semester" error={form.errors.semester}>
                            <Select value={form.data.semester} required onChange={(event) => form.setData('semester', event.target.value)}>
                                <option value="1">1 (Ganjil)</option>
                                <option value="2">2 (Genap)</option>
                            </Select>
                        </FormField>
                        <FormField label="Tipe File" error={form.errors.tipe}>
                            <Select value={form.data.tipe} required onChange={(event) => form.setData('tipe', event.target.value as ArsipTipe)}>
                                <option value="Leger">Leger</option>
                                <option value="RDM">RDM (Raport Digital)</option>
                                <option value="Lainnya">Lainnya</option>
                            </Select>
                        </FormField>
                    </div>
                    <FileUploadField label="File Arsip" name="file_arsip" accept=".pdf,.xlsx,.xls,.zip" hint="PDF, Excel, atau ZIP. Maksimal 5MB." error={form.errors.file_arsip} onChange={(file) => form.setData('file_arsip', file)} />
                    <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <Button type="button" variant="outline" disabled={form.processing} onClick={() => setUploadOpen(false)}>Batal</Button>
                        <Button type="submit" disabled={form.processing}>
                            {form.processing ? <Loader2 size={16} className="animate-spin" /> : <Upload size={17} />}
                            Simpan Arsip
                        </Button>
                    </div>
                </form>
            </Dialog>

            <ConfirmDeleteDialog open={Boolean(deleteTarget)} message={`Hapus arsip ${deleteTarget?.nama_arsip ?? ''}?`} onClose={() => setDeleteTarget(null)} onConfirm={destroy} />
            <ConfirmDeleteDialog open={bulkOpen} message={`Hapus ${selectedIds.length} arsip akademik terpilih?`} onClose={() => setBulkOpen(false)} onConfirm={bulkDestroy} />
        </AppLayout>
    );
}
