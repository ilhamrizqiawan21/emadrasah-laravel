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
import { Select } from '@/Components/ui/select';

interface ParentOption {
    id: number;
    nama_mapel: string;
}

interface MapelFormData {
    nama_mapel: string;
    jp_per_sesi: string;
    parent_id: string;
    urut: string;
}

interface MapelRecord {
    id: number;
    nama_mapel: string;
    jp_per_sesi: number | null;
    parent_id: number | null;
    urut: number | null;
}

interface MapelFormProps {
    mapel?: MapelRecord;
    parents: ParentOption[];
}

export default function MapelForm({ mapel, parents }: MapelFormProps) {
    const isEdit = Boolean(mapel);
    const [confirmOpen, setConfirmOpen] = useState(false);
    const { data, setData, post, put, processing, errors } = useForm<MapelFormData>({
        nama_mapel: mapel?.nama_mapel ?? '',
        jp_per_sesi: mapel?.jp_per_sesi?.toString() ?? '2',
        parent_id: mapel?.parent_id?.toString() ?? '',
        urut: mapel?.urut?.toString() ?? '1',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();

        if (mapel) {
            put(`/mapel/${mapel.id}`);
        } else {
            post('/mapel');
        }
    };

    const destroy = () => {
        if (!mapel) {
            return;
        }

        router.delete(`/mapel/${mapel.id}`, {
            onFinish: () => setConfirmOpen(false),
        });
    };

    return (
        <AppLayout title={isEdit ? 'Edit Mata Pelajaran' : 'Tambah Mata Pelajaran'}>
            <Head title={isEdit ? 'Edit Mata Pelajaran' : 'Tambah Mata Pelajaran'} />
            <div className="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <Link href="/mapel">
                    <Button variant="outline" size="sm">
                        <ArrowLeft size={16} />
                        Kembali
                    </Button>
                </Link>
                {mapel ? (
                    <Button variant="danger" size="sm" onClick={() => setConfirmOpen(true)}>
                        <Trash2 size={16} />
                        Hapus Mapel
                    </Button>
                ) : null}
            </div>

            <ResourceForm
                title={isEdit ? `Edit Mapel: ${mapel?.nama_mapel}` : 'Tambah Mata Pelajaran Baru'}
                onSubmit={submit}
                actions={
                    <>
                        <Link href="/mapel">
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
                <FormField label="Nama Mata Pelajaran" error={errors.nama_mapel} hint="Contoh: Matematika, Bahasa Indonesia, IPA.">
                    <Input value={data.nama_mapel} required onChange={(event) => setData('nama_mapel', event.target.value)} />
                </FormField>
                <div className="grid gap-4 md:grid-cols-3">
                    <FormField label="Kelompok Mapel" error={errors.parent_id}>
                        <Select value={data.parent_id} onChange={(event) => setData('parent_id', event.target.value)}>
                            <option value="">Mapel induk</option>
                            {parents.map((parent) => (
                                <option key={parent.id} value={parent.id}>
                                    {parent.nama_mapel}
                                </option>
                            ))}
                        </Select>
                    </FormField>
                    <FormField label="JP per Sesi" error={errors.jp_per_sesi}>
                        <Input
                            type="number"
                            min={0}
                            value={data.jp_per_sesi}
                            onChange={(event) => setData('jp_per_sesi', event.target.value)}
                        />
                    </FormField>
                    <FormField label="Urutan" error={errors.urut}>
                        <Input
                            type="number"
                            min={1}
                            value={data.urut}
                            onChange={(event) => setData('urut', event.target.value)}
                        />
                    </FormField>
                </div>
            </ResourceForm>

            <ConfirmDeleteDialog
                open={confirmOpen}
                message={`Hapus mata pelajaran ${mapel?.nama_mapel ?? ''}? Jadwal terkait dapat ikut terdampak.`}
                processing={processing}
                onClose={() => setConfirmOpen(false)}
                onConfirm={destroy}
            />
        </AppLayout>
    );
}
