import { Head, Link, router, useForm } from '@inertiajs/react';
import { ArrowLeft, Loader2, RotateCcw, Save } from 'lucide-react';
import { FormEvent, useState } from 'react';
import { ConfirmDeleteDialog } from '@/Components/ConfirmDeleteDialog';
import { DataTable, DataTableColumn } from '@/Components/DataTable';
import { ErrorSummary } from '@/Components/ErrorSummary';
import { FormField } from '@/Components/FormField';
import AppLayout from '@/Layouts/AppLayout';
import { Pagination } from '@/Components/Pagination';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Select } from '@/Components/ui/select';
import { formatDate, formatNumber, formatStatus } from '@/lib/format';
import { Paginated } from '@/types';

type PeminjamanStatus = 'dipinjam' | 'dikembalikan';

interface SaranaSummary {
    id: number;
    kode_sarana: string;
    nama_sarana: string;
    jumlah: number;
    stok_tersedia: number;
    kondisi: string;
    lokasi_ruang: string | null;
}

interface PeminjamanRow {
    id: number;
    peminjam: string;
    tipe_peminjam: 'guru' | 'siswa';
    tanggal_pinjam: string;
    tanggal_kembali: string | null;
    denda: string | number | null;
    status: PeminjamanStatus;
}

interface PeminjamanProps {
    sarana: SaranaSummary;
    peminjaman: Paginated<PeminjamanRow>;
}

function today() {
    return new Date().toISOString().slice(0, 10);
}

function formatCurrency(value: string | number | null | undefined) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value ?? 0));
}

export default function SaranaPeminjaman({ sarana, peminjaman }: PeminjamanProps) {
    const [returnTarget, setReturnTarget] = useState<PeminjamanRow | null>(null);
    const { data, setData, post, processing, errors, reset } = useForm({
        peminjam: '',
        tipe_peminjam: 'guru',
        tanggal_pinjam: today(),
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        post(`/sarana/${sarana.id}/peminjaman`, {
            preserveScroll: true,
            onSuccess: () => reset(),
        });
    };

    const markReturned = () => {
        if (!returnTarget) {
            return;
        }

        router.put(`/peminjaman/${returnTarget.id}/kembali`, {}, {
            preserveScroll: true,
            onFinish: () => setReturnTarget(null),
        });
    };

    const columns: Array<DataTableColumn<PeminjamanRow>> = [
        {
            key: 'peminjam',
            label: 'Peminjam',
            render: (item) => <span className="font-semibold text-slate-900">{item.peminjam}</span>,
        },
        {
            key: 'tipe_peminjam',
            label: 'Tipe',
            render: (item) => formatStatus(item.tipe_peminjam),
        },
        {
            key: 'tanggal_pinjam',
            label: 'Tanggal Pinjam',
            render: (item) => formatDate(item.tanggal_pinjam),
        },
        {
            key: 'tanggal_kembali',
            label: 'Tanggal Kembali',
            render: (item) => formatDate(item.tanggal_kembali),
        },
        {
            key: 'status',
            label: 'Status',
            render: (item) => <Badge variant={item.status === 'dipinjam' ? 'warning' : 'success'}>{formatStatus(item.status)}</Badge>,
        },
        {
            key: 'denda',
            label: 'Denda',
            render: (item) => formatCurrency(item.denda),
        },
        {
            key: 'actions',
            label: 'Aksi',
            headerClassName: 'w-24 text-right',
            className: 'text-right',
            render: (item) => item.status === 'dipinjam' ? (
                <Button variant="outline" size="icon" title="Kembalikan" aria-label={`Kembalikan ${item.peminjam}`} onClick={() => setReturnTarget(item)}>
                    <RotateCcw size={16} />
                </Button>
            ) : '-',
        },
    ];

    return (
        <AppLayout title="Peminjaman Sarana">
            <Head title="Peminjaman Sarana" />
            <div className="mb-4">
                <Link href="/sarana">
                    <Button variant="outline" size="sm">
                        <ArrowLeft size={16} />
                        Kembali ke Daftar Sarana
                    </Button>
                </Link>
            </div>

            <Card className="mb-4">
                <CardHeader>
                    <CardTitle>{sarana.nama_sarana} ({sarana.kode_sarana})</CardTitle>
                    <p className="mt-1 text-sm font-medium text-slate-500">
                        Stok tersedia {formatNumber(sarana.stok_tersedia)} dari {formatNumber(sarana.jumlah)} · Kondisi {formatStatus(sarana.kondisi)} · {sarana.lokasi_ruang ?? 'Lokasi belum diisi'}
                    </p>
                </CardHeader>
            </Card>

            <div className="grid gap-4 xl:grid-cols-[380px_1fr]">
                <Card>
                    <CardHeader>
                        <CardTitle>Form Peminjaman Baru</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form className="grid gap-4" onSubmit={submit}>
                            <ErrorSummary errors={errors} />
                            <FormField label="Nama Peminjam" error={errors.peminjam}>
                                <Input value={data.peminjam} required onChange={(event) => setData('peminjam', event.target.value)} />
                            </FormField>
                            <FormField label="Tipe Peminjam" error={errors.tipe_peminjam}>
                                <Select value={data.tipe_peminjam} onChange={(event) => setData('tipe_peminjam', event.target.value)}>
                                    <option value="guru">Guru</option>
                                    <option value="siswa">Siswa</option>
                                </Select>
                            </FormField>
                            <FormField label="Tanggal Pinjam" error={errors.tanggal_pinjam}>
                                <Input type="date" value={data.tanggal_pinjam} required onChange={(event) => setData('tanggal_pinjam', event.target.value)} />
                            </FormField>
                            <div className="flex justify-end">
                                <Button type="submit" disabled={processing || sarana.stok_tersedia < 1}>
                                    {processing ? <Loader2 size={16} className="animate-spin" /> : <Save size={17} />}
                                    Catat Peminjaman
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Riwayat Peminjaman</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <DataTable
                            data={peminjaman.data}
                            columns={columns}
                            emptyTitle="Belum ada riwayat peminjaman"
                            emptyDescription="Riwayat peminjaman sarana ini akan tampil di sini."
                        />
                        <div className="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div className="text-sm font-medium text-slate-500">
                                Menampilkan {peminjaman.from ?? 0}-{peminjaman.to ?? 0} dari {peminjaman.total} catatan
                            </div>
                            <Pagination links={peminjaman.links} />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <ConfirmDeleteDialog
                open={Boolean(returnTarget)}
                message={`Tandai peminjaman oleh ${returnTarget?.peminjam ?? ''} sebagai dikembalikan?`}
                onClose={() => setReturnTarget(null)}
                onConfirm={markReturned}
            />
        </AppLayout>
    );
}
