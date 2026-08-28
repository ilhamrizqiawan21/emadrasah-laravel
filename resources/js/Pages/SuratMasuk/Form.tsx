import { Head, Link, router, useForm } from '@inertiajs/react';
import { ArrowLeft, Save, Trash2 } from 'lucide-react';
import { FormEvent, useState } from 'react';
import { ConfirmDeleteDialog } from '@/Components/ConfirmDeleteDialog';
import { ErrorSummary } from '@/Components/ErrorSummary';
import { FileUploadField } from '@/Components/FileUploadField';
import { FormField } from '@/Components/FormField';
import { ResourceForm } from '@/Components/ResourceForm';
import AppLayout from '@/Layouts/AppLayout';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Select } from '@/Components/ui/select';
import { Textarea } from '@/Components/ui/textarea';

interface SuratMasukRecord {
    id: number;
    nomor_agenda: string;
    asal_surat: string;
    nomor_surat: string | null;
    perihal: string;
    tanggal_terima: string | null;
    tanggal_surat: string | null;
    disposisi: string | null;
    status: 'diterima' | 'diproses' | 'selesai';
    file_scan_url: string | null;
}

interface SuratMasukFormData {
    asal_surat: string;
    nomor_surat: string;
    perihal: string;
    tanggal_terima: string;
    tanggal_surat: string;
    disposisi: string;
    status: 'diterima' | 'diproses' | 'selesai';
    file_scan: File | null;
}

interface SuratMasukFormProps {
    suratMasuk?: SuratMasukRecord;
}

function today() {
    return new Date().toISOString().slice(0, 10);
}

export default function SuratMasukForm({ suratMasuk }: SuratMasukFormProps) {
    const isEdit = Boolean(suratMasuk);
    const [confirmOpen, setConfirmOpen] = useState(false);
    const { data, setData, post, processing, errors, transform } = useForm<SuratMasukFormData>({
        asal_surat: suratMasuk?.asal_surat ?? '',
        nomor_surat: suratMasuk?.nomor_surat ?? '',
        perihal: suratMasuk?.perihal ?? '',
        tanggal_terima: suratMasuk?.tanggal_terima ?? today(),
        tanggal_surat: suratMasuk?.tanggal_surat ?? '',
        disposisi: suratMasuk?.disposisi ?? '',
        status: suratMasuk?.status ?? 'diterima',
        file_scan: null,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (suratMasuk) {
            transform((data) => ({ ...data, _method: 'PUT' }));
            post(`/surat-masuk/${suratMasuk.id}`, { forceFormData: true });
        } else {
            transform((data) => data);
            post('/surat-masuk', { forceFormData: true });
        }
    };

    const destroy = () => {
        if (!suratMasuk) {
            return;
        }

        router.delete(`/surat-masuk/${suratMasuk.id}`, {
            onFinish: () => setConfirmOpen(false),
        });
    };

    return (
        <AppLayout title={isEdit ? 'Edit Surat Masuk' : 'Tambah Surat Masuk'}>
            <Head title={isEdit ? 'Edit Surat Masuk' : 'Tambah Surat Masuk'} />
            <div className="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <Link href="/surat-masuk">
                    <Button variant="outline" size="sm">
                        <ArrowLeft size={16} />
                        Kembali
                    </Button>
                </Link>
                {suratMasuk ? (
                    <Button variant="danger" size="sm" onClick={() => setConfirmOpen(true)}>
                        <Trash2 size={16} />
                        Hapus Surat
                    </Button>
                ) : null}
            </div>

            <ResourceForm
                title={isEdit ? `Edit Surat Masuk: ${suratMasuk?.nomor_agenda}` : 'Form Tambah Surat Masuk'}
                onSubmit={submit}
                actions={
                    <>
                        <Link href="/surat-masuk">
                            <Button type="button" variant="outline" disabled={processing}>Batal</Button>
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
                    <FormField label="Asal Surat" error={errors.asal_surat}>
                        <Input value={data.asal_surat} required onChange={(event) => setData('asal_surat', event.target.value)} />
                    </FormField>
                    <FormField label="Nomor Surat" error={errors.nomor_surat}>
                        <Input value={data.nomor_surat} onChange={(event) => setData('nomor_surat', event.target.value)} />
                    </FormField>
                </div>
                <FormField label="Perihal" error={errors.perihal}>
                    <Input value={data.perihal} required onChange={(event) => setData('perihal', event.target.value)} />
                </FormField>
                <div className="grid gap-4 md:grid-cols-3">
                    <FormField label="Tanggal Terima" error={errors.tanggal_terima}>
                        <Input type="date" value={data.tanggal_terima} required onChange={(event) => setData('tanggal_terima', event.target.value)} />
                    </FormField>
                    <FormField label="Tanggal Surat" error={errors.tanggal_surat}>
                        <Input type="date" value={data.tanggal_surat} onChange={(event) => setData('tanggal_surat', event.target.value)} />
                    </FormField>
                    {suratMasuk ? (
                        <FormField label="Status" error={errors.status}>
                            <Select value={data.status} onChange={(event) => setData('status', event.target.value as SuratMasukFormData['status'])}>
                                <option value="diterima">Diterima</option>
                                <option value="diproses">Diproses</option>
                                <option value="selesai">Selesai</option>
                            </Select>
                        </FormField>
                    ) : null}
                </div>
                {suratMasuk?.file_scan_url ? (
                    <a href={suratMasuk.file_scan_url} target="_blank" rel="noreferrer" className="text-sm font-semibold text-blue-700">
                        Lihat file scan saat ini
                    </a>
                ) : null}
                <FileUploadField label="File Scan" name="file_scan" accept=".pdf,.jpg,.jpeg,.png" hint="PDF, JPG, JPEG, atau PNG. Maksimal 2MB." error={errors.file_scan} onChange={(file) => setData('file_scan', file)} />
                <FormField label="Disposisi" error={errors.disposisi}>
                    <Textarea value={data.disposisi} onChange={(event) => setData('disposisi', event.target.value)} />
                </FormField>
            </ResourceForm>

            <ConfirmDeleteDialog open={confirmOpen} message={`Hapus surat masuk ${suratMasuk?.nomor_agenda ?? ''}?`} processing={processing} onClose={() => setConfirmOpen(false)} onConfirm={destroy} />
        </AppLayout>
    );
}
