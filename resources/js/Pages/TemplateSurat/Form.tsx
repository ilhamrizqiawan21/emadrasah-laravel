import { Head, Link, router, useForm } from '@inertiajs/react';
import { ArrowLeft, Save, Trash2 } from 'lucide-react';
import { FormEvent, useState } from 'react';
import { ConfirmDeleteDialog } from '@/Components/ConfirmDeleteDialog';
import { ErrorSummary } from '@/Components/ErrorSummary';
import { FormField } from '@/Components/FormField';
import { ResourceForm } from '@/Components/ResourceForm';
import AppLayout from '@/Layouts/AppLayout';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Textarea } from '@/Components/ui/textarea';

interface TemplateRecord {
    id: number;
    nama_template: string;
    konten: string;
}

interface TemplateFormData {
    nama_template: string;
    konten: string;
}

interface TemplateFormProps {
    templateSurat?: TemplateRecord;
}

export default function TemplateSuratForm({ templateSurat }: TemplateFormProps) {
    const isEdit = Boolean(templateSurat);
    const [confirmOpen, setConfirmOpen] = useState(false);
    const { data, setData, post, put, processing, errors } = useForm<TemplateFormData>({
        nama_template: templateSurat?.nama_template ?? '',
        konten: templateSurat?.konten ?? '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (templateSurat) {
            put(`/template-surat/${templateSurat.id}`);
        } else {
            post('/template-surat');
        }
    };

    const destroy = () => {
        if (!templateSurat) {
            return;
        }

        router.delete(`/template-surat/${templateSurat.id}`, {
            onFinish: () => setConfirmOpen(false),
        });
    };

    return (
        <AppLayout title={isEdit ? 'Edit Template Surat' : 'Tambah Template Surat'}>
            <Head title={isEdit ? 'Edit Template Surat' : 'Tambah Template Surat'} />
            <div className="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <Link href="/template-surat">
                    <Button variant="outline" size="sm">
                        <ArrowLeft size={16} />
                        Kembali
                    </Button>
                </Link>
                {templateSurat ? (
                    <Button variant="danger" size="sm" onClick={() => setConfirmOpen(true)}>
                        <Trash2 size={16} />
                        Hapus Template
                    </Button>
                ) : null}
            </div>

            <div className="grid gap-4 xl:grid-cols-[minmax(0,1fr)_20rem]">
                <ResourceForm
                    title={isEdit ? `Edit Template: ${templateSurat?.nama_template}` : 'Form Tambah Template Surat'}
                    onSubmit={submit}
                    actions={
                        <>
                            <Link href="/template-surat">
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
                    <FormField label="Nama Template" error={errors.nama_template} hint="Contoh: Undangan Rapat, Surat Tugas, atau Keterangan Siswa.">
                        <Input value={data.nama_template} required onChange={(event) => setData('nama_template', event.target.value)} />
                    </FormField>
                    <FormField label="Konten Template" error={errors.konten} hint="Gunakan placeholder seperti [TANGGAL], [NAMA], atau [NOMOR_SURAT].">
                        <Textarea className="min-h-80" value={data.konten} required onChange={(event) => setData('konten', event.target.value)} />
                    </FormField>
                </ResourceForm>

                <Card>
                    <CardContent>
                        <div className="font-semibold text-slate-900">Placeholder Umum</div>
                        <div className="mt-3 grid gap-2 text-sm font-medium text-slate-600">
                            <code className="rounded bg-slate-100 px-2 py-1">[TANGGAL]</code>
                            <code className="rounded bg-slate-100 px-2 py-1">[NAMA]</code>
                            <code className="rounded bg-slate-100 px-2 py-1">[NOMOR_SURAT]</code>
                            <code className="rounded bg-slate-100 px-2 py-1">[PERIHAL]</code>
                        </div>
                        {templateSurat ? (
                            <div className="mt-5 rounded-md border border-rose-200 bg-rose-50 p-3">
                                <div className="font-semibold text-rose-800">Zona Hapus</div>
                                <p className="mt-1 text-sm font-medium text-rose-700">Menghapus template tidak mempengaruhi surat yang sudah dibuat.</p>
                            </div>
                        ) : null}
                    </CardContent>
                </Card>
            </div>

            <ConfirmDeleteDialog open={confirmOpen} message={`Hapus template ${templateSurat?.nama_template ?? ''}?`} processing={processing} onClose={() => setConfirmOpen(false)} onConfirm={destroy} />
        </AppLayout>
    );
}
