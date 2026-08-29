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

interface KategoriSaranaRecord {
    id: number;
    nama_kategori: string;
}

interface KategoriSaranaFormProps {
    kategoriSarana?: KategoriSaranaRecord;
}

export default function KategoriSaranaForm({ kategoriSarana }: KategoriSaranaFormProps) {
    const isEdit = Boolean(kategoriSarana);
    const [confirmOpen, setConfirmOpen] = useState(false);
    const { data, setData, post, put, processing, errors } = useForm({
        nama_kategori: kategoriSarana?.nama_kategori ?? '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();

        if (kategoriSarana) {
            put(`/kategori-sarana/${kategoriSarana.id}`);
        } else {
            post('/kategori-sarana');
        }
    };

    const destroy = () => {
        if (!kategoriSarana) {
            return;
        }

        router.delete(`/kategori-sarana/${kategoriSarana.id}`, {
            onFinish: () => setConfirmOpen(false),
        });
    };

    return (
        <AppLayout title={isEdit ? 'Edit Kategori Sarana' : 'Tambah Kategori Sarana'}>
            <Head title={isEdit ? 'Edit Kategori Sarana' : 'Tambah Kategori Sarana'} />
            <div className="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <Link href="/kategori-sarana">
                    <Button variant="outline" size="sm">
                        <ArrowLeft size={16} />
                        Kembali
                    </Button>
                </Link>
                {kategoriSarana ? (
                    <Button variant="danger" size="sm" onClick={() => setConfirmOpen(true)}>
                        <Trash2 size={16} />
                        Hapus Kategori
                    </Button>
                ) : null}
            </div>

            <ResourceForm
                title={isEdit ? `Edit Kategori: ${kategoriSarana?.nama_kategori}` : 'Tambah Kategori Sarana'}
                onSubmit={submit}
                actions={
                    <>
                        <Link href="/kategori-sarana">
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
                <FormField label="Nama Kategori" error={errors.nama_kategori} hint="Contoh: Elektronik, Furniture, Olahraga, atau Laboratorium.">
                    <Input value={data.nama_kategori} required onChange={(event) => setData('nama_kategori', event.target.value)} />
                </FormField>
            </ResourceForm>

            <ConfirmDeleteDialog
                open={confirmOpen}
                message={`Hapus kategori ${kategoriSarana?.nama_kategori ?? ''}? Sistem akan menolak jika kategori masih digunakan.`}
                processing={processing}
                onClose={() => setConfirmOpen(false)}
                onConfirm={destroy}
            />
        </AppLayout>
    );
}
