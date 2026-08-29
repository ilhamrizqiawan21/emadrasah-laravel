import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Loader2, Save } from 'lucide-react';
import { FormEvent } from 'react';
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
import { Textarea } from '@/Components/ui/textarea';
import { formatDate, formatNumber, formatStatus } from '@/lib/format';
import { Paginated } from '@/types';

type PemeliharaanStatus = 'proses' | 'selesai';

interface SaranaSummary {
    id: number;
    kode_sarana: string;
    nama_sarana: string;
    jumlah: number;
    stok_tersedia: number;
    kondisi: string;
    lokasi_ruang: string | null;
}

interface PemeliharaanRow {
    id: number;
    tanggal_pemeliharaan: string;
    biaya: string | number | null;
    keterangan: string | null;
    status: PemeliharaanStatus | null;
    tanggal_selesai: string | null;
    teknisi: string | null;
}

interface PemeliharaanProps {
    sarana: SaranaSummary;
    pemeliharaan: Paginated<PemeliharaanRow>;
}

function today() {
    return new Date().toISOString().slice(0, 10);
}

function formatCurrency(value: string | number | null | undefined) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value ?? 0));
}

export default function SaranaPemeliharaan({ sarana, pemeliharaan }: PemeliharaanProps) {
    const { data, setData, post, processing, errors, reset } = useForm({
        tanggal_pemeliharaan: today(),
        biaya: '0',
        keterangan: '',
        status: 'proses',
        tanggal_selesai: '',
        teknisi: '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        post(`/sarana/${sarana.id}/pemeliharaan`, {
            preserveScroll: true,
            onSuccess: () => reset(),
        });
    };

    const columns: Array<DataTableColumn<PemeliharaanRow>> = [
        {
            key: 'tanggal_pemeliharaan',
            label: 'Tanggal',
            render: (item) => formatDate(item.tanggal_pemeliharaan),
        },
        {
            key: 'biaya',
            label: 'Biaya',
            render: (item) => formatCurrency(item.biaya),
        },
        {
            key: 'teknisi',
            label: 'Teknisi',
            render: (item) => item.teknisi ?? '-',
        },
        {
            key: 'status',
            label: 'Status',
            render: (item) => item.status ? <Badge variant={item.status === 'selesai' ? 'success' : 'warning'}>{formatStatus(item.status)}</Badge> : '-',
        },
        {
            key: 'tanggal_selesai',
            label: 'Selesai',
            render: (item) => formatDate(item.tanggal_selesai),
        },
        {
            key: 'keterangan',
            label: 'Keterangan',
            render: (item) => item.keterangan ?? '-',
        },
    ];

    return (
        <AppLayout title="Pemeliharaan Sarana">
            <Head title="Pemeliharaan Sarana" />
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
                        <CardTitle>Catat Pemeliharaan</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form className="grid gap-4" onSubmit={submit}>
                            <ErrorSummary errors={errors} />
                            <FormField label="Tanggal Pemeliharaan" error={errors.tanggal_pemeliharaan}>
                                <Input type="date" value={data.tanggal_pemeliharaan} required onChange={(event) => setData('tanggal_pemeliharaan', event.target.value)} />
                            </FormField>
                            <FormField label="Biaya" error={errors.biaya}>
                                <Input type="number" min={0} step={1000} value={data.biaya} onChange={(event) => setData('biaya', event.target.value)} />
                            </FormField>
                            <FormField label="Status" error={errors.status}>
                                <Select value={data.status} onChange={(event) => setData('status', event.target.value)}>
                                    <option value="proses">Proses</option>
                                    <option value="selesai">Selesai</option>
                                </Select>
                            </FormField>
                            <FormField label="Tanggal Selesai" error={errors.tanggal_selesai}>
                                <Input type="date" value={data.tanggal_selesai} onChange={(event) => setData('tanggal_selesai', event.target.value)} />
                            </FormField>
                            <FormField label="Teknisi / Pelaksana" error={errors.teknisi}>
                                <Input value={data.teknisi} onChange={(event) => setData('teknisi', event.target.value)} />
                            </FormField>
                            <FormField label="Keterangan" error={errors.keterangan}>
                                <Textarea rows={3} value={data.keterangan} onChange={(event) => setData('keterangan', event.target.value)} />
                            </FormField>
                            <div className="flex justify-end">
                                <Button type="submit" disabled={processing}>
                                    {processing ? <Loader2 size={16} className="animate-spin" /> : <Save size={17} />}
                                    Simpan
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Riwayat Pemeliharaan</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <DataTable
                            data={pemeliharaan.data}
                            columns={columns}
                            emptyTitle="Belum ada catatan pemeliharaan"
                            emptyDescription="Riwayat pemeliharaan sarana ini akan tampil di sini."
                        />
                        <div className="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div className="text-sm font-medium text-slate-500">
                                Menampilkan {pemeliharaan.from ?? 0}-{pemeliharaan.to ?? 0} dari {pemeliharaan.total} catatan
                            </div>
                            <Pagination links={pemeliharaan.links} />
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
