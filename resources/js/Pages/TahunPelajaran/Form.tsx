import { Head, Link, router, useForm } from '@inertiajs/react';
import { ArrowLeft, Save, Trash2 } from 'lucide-react';
import { FormEvent, useState } from 'react';
import { ConfirmDeleteDialog } from '@/Components/ConfirmDeleteDialog';
import { ErrorSummary } from '@/Components/ErrorSummary';
import { FormField } from '@/Components/FormField';
import { ResourceForm } from '@/Components/ResourceForm';
import AppLayout from '@/Layouts/AppLayout';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Switch } from '@/Components/ui/switch';

interface TahunPelajaranFormData {
    kode: string;
    nama: string;
    is_aktif: boolean;
}

interface TahunPelajaranRecord {
    id: number;
    kode: string;
    nama: string;
    is_aktif: boolean;
}

interface TahunPelajaranFormProps {
    tahunPelajaran?: TahunPelajaranRecord;
}

function maskYearCode(value: string) {
    const digits = value.replace(/\D/g, '').slice(0, 8);

    if (digits.length <= 4) {
        return digits;
    }

    return `${digits.slice(0, 4)}/${digits.slice(4)}`;
}

export default function TahunPelajaranForm({ tahunPelajaran }: TahunPelajaranFormProps) {
    const isEdit = Boolean(tahunPelajaran);
    const [confirmOpen, setConfirmOpen] = useState(false);
    const { data, setData, post, put, processing, errors } = useForm<TahunPelajaranFormData>({
        kode: tahunPelajaran?.kode ?? '',
        nama: tahunPelajaran?.nama ?? '',
        is_aktif: tahunPelajaran?.is_aktif ?? false,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();

        if (tahunPelajaran) {
            put(`/tahun-pelajaran/${tahunPelajaran.id}`);
        } else {
            post('/tahun-pelajaran');
        }
    };

    const destroy = () => {
        if (!tahunPelajaran) {
            return;
        }

        router.delete(`/tahun-pelajaran/${tahunPelajaran.id}`, {
            onFinish: () => setConfirmOpen(false),
        });
    };

    return (
        <AppLayout title={isEdit ? 'Ubah Tahun Pelajaran' : 'Tambah Tahun Pelajaran'}>
            <Head title={isEdit ? 'Ubah Tahun Pelajaran' : 'Tambah Tahun Pelajaran'} />
            <div className="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <Link href="/tahun-pelajaran">
                    <Button variant="outline" size="sm">
                        <ArrowLeft size={16} />
                        Kembali
                    </Button>
                </Link>
                {tahunPelajaran ? (
                    <Button variant="danger" size="sm" onClick={() => setConfirmOpen(true)}>
                        <Trash2 size={16} />
                        Hapus Tahun
                    </Button>
                ) : null}
            </div>

            <ResourceForm
                title={isEdit ? `Ubah Tahun Pelajaran: ${tahunPelajaran?.kode}` : 'Tambah Tahun Pelajaran'}
                onSubmit={submit}
                actions={
                    <>
                        <Link href="/tahun-pelajaran">
                            <Button type="button" variant="outline" disabled={processing}>
                                Batal
                            </Button>
                        </Link>
                        <Button type="submit" disabled={processing}>
                            <Save size={17} />
                            Simpan
                        </Button>
                    </>
                }
            >
                <ErrorSummary errors={errors} />
                <div className="grid gap-4 md:grid-cols-2">
                    <FormField label="Kode" error={errors.kode} hint="Format umum: 2026/2027.">
                        <Input
                            value={data.kode}
                            required
                            inputMode="numeric"
                            maxLength={9}
                            onChange={(event) => setData('kode', maskYearCode(event.target.value))}
                        />
                    </FormField>
                    <FormField label="Nama" error={errors.nama}>
                        <Input value={data.nama} required onChange={(event) => setData('nama', event.target.value)} />
                    </FormField>
                </div>
                <div className="flex items-center justify-between gap-4 rounded-md border border-slate-200 p-4">
                    <div>
                        <div className="text-sm font-semibold text-slate-900">Aktifkan tahun pelajaran ini</div>
                        <div className="mt-1 text-xs font-medium text-slate-500">Tahun aktif diprioritaskan untuk jadwal, rapor, dan arsip.</div>
                    </div>
                    <Switch checked={data.is_aktif} onCheckedChange={(checked) => setData('is_aktif', checked)} aria-label="Aktifkan tahun pelajaran" />
                </div>
            </ResourceForm>

            <ConfirmDeleteDialog
                open={confirmOpen}
                message={`Hapus tahun pelajaran ${tahunPelajaran?.kode ?? ''}? Pastikan tidak sedang dipakai data aktif.`}
                processing={processing}
                onClose={() => setConfirmOpen(false)}
                onConfirm={destroy}
            />
        </AppLayout>
    );
}
