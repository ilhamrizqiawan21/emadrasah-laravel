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

interface SuratKeluarRecord {
    id: number;
    nomor_surat: string;
    tujuan: string;
    perihal: string;
    tanggal_kirim: string | null;
    lampiran: string | null;
    file_draft_url: string | null;
}

interface SiswaOption {
    id: number;
    nama_lengkap: string;
    nis: string;
    nisn: string | null;
}

interface SuratKeluarFormData {
    nomor_surat: string;
    tujuan: string;
    perihal: string;
    tanggal_kirim: string;
    lampiran: string;
    file_draft: File | null;
}

interface SuratKeluarFormProps {
    suratKeluar?: SuratKeluarRecord;
    siswa: SiswaOption[];
}

function today() {
    return new Date().toISOString().slice(0, 10);
}

export default function SuratKeluarForm({ suratKeluar, siswa }: SuratKeluarFormProps) {
    const isEdit = Boolean(suratKeluar);
    const [confirmOpen, setConfirmOpen] = useState(false);
    const { data, setData, post, processing, errors, transform } = useForm<SuratKeluarFormData>({
        nomor_surat: suratKeluar?.nomor_surat ?? '',
        tujuan: suratKeluar?.tujuan ?? '',
        perihal: suratKeluar?.perihal ?? '',
        tanggal_kirim: suratKeluar?.tanggal_kirim ?? today(),
        lampiran: suratKeluar?.lampiran ?? '',
        file_draft: null,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (suratKeluar) {
            transform((data) => ({ ...data, _method: 'PUT' }));
            post(`/surat-keluar/${suratKeluar.id}`, { forceFormData: true });
        } else {
            transform((data) => data);
            post('/surat-keluar', { forceFormData: true });
        }
    };

    const destroy = () => {
        if (!suratKeluar) {
            return;
        }

        router.delete(`/surat-keluar/${suratKeluar.id}`, {
            onFinish: () => setConfirmOpen(false),
        });
    };

    return (
        <AppLayout title={isEdit ? 'Edit Surat Keluar' : 'Tambah Surat Keluar'}>
            <Head title={isEdit ? 'Edit Surat Keluar' : 'Tambah Surat Keluar'} />
            <div className="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <Link href="/surat-keluar">
                    <Button variant="outline" size="sm">
                        <ArrowLeft size={16} />
                        Kembali
                    </Button>
                </Link>
                {suratKeluar ? (
                    <Button variant="danger" size="sm" onClick={() => setConfirmOpen(true)}>
                        <Trash2 size={16} />
                        Hapus Surat
                    </Button>
                ) : null}
            </div>

            <ResourceForm
                title={isEdit ? `Edit Surat Keluar: ${suratKeluar?.nomor_surat}` : 'Form Tambah Surat Keluar'}
                onSubmit={submit}
                actions={
                    <>
                        <Link href="/surat-keluar">
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
                <FormField label="Otomatisasi Data Siswa" hint="Memilih siswa akan otomatis mengisi kolom tujuan.">
                    <Select value="" onChange={(event) => setData('tujuan', event.target.value)}>
                        <option value="">Pilih siswa untuk tujuan otomatis</option>
                        {siswa.map((item) => (
                            <option key={item.id} value={`${item.nama_lengkap} (NIS: ${item.nis})`}>
                                {item.nama_lengkap} - {item.nis}
                            </option>
                        ))}
                    </Select>
                </FormField>
                <div className="grid gap-4 md:grid-cols-2">
                    <FormField label="Nomor Surat" error={errors.nomor_surat}>
                        <Input value={data.nomor_surat} required placeholder="Contoh: 001/MTs/XI/2026" onChange={(event) => setData('nomor_surat', event.target.value)} />
                    </FormField>
                    <FormField label="Tujuan" error={errors.tujuan}>
                        <Input value={data.tujuan} required onChange={(event) => setData('tujuan', event.target.value)} />
                    </FormField>
                </div>
                <FormField label="Perihal" error={errors.perihal}>
                    <Input value={data.perihal} required onChange={(event) => setData('perihal', event.target.value)} />
                </FormField>
                <div className="grid gap-4 md:grid-cols-2">
                    <FormField label="Tanggal Kirim" error={errors.tanggal_kirim}>
                        <Input type="date" value={data.tanggal_kirim} required onChange={(event) => setData('tanggal_kirim', event.target.value)} />
                    </FormField>
                    <FormField label="Lampiran" error={errors.lampiran}>
                        <Input value={data.lampiran} placeholder="Contoh: 2 lembar" onChange={(event) => setData('lampiran', event.target.value)} />
                    </FormField>
                </div>
                {suratKeluar?.file_draft_url ? (
                    <a href={suratKeluar.file_draft_url} target="_blank" rel="noreferrer" className="text-sm font-semibold text-blue-700">
                        Lihat file draft saat ini
                    </a>
                ) : null}
                <FileUploadField label="File Draft" name="file_draft" accept=".pdf,.doc,.docx" hint="PDF, DOC, atau DOCX. Maksimal 2MB." error={errors.file_draft} onChange={(file) => setData('file_draft', file)} />
            </ResourceForm>

            <ConfirmDeleteDialog open={confirmOpen} message={`Hapus surat keluar ${suratKeluar?.nomor_surat ?? ''}?`} processing={processing} onClose={() => setConfirmOpen(false)} onConfirm={destroy} />
        </AppLayout>
    );
}
