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

interface JamPelajaranFormData {
    hari: string;
    sesi_ke: string;
    jam_mulai: string;
    jam_selesai: string;
}

interface JamPelajaranRecord {
    id: number;
    hari: string;
    sesi_ke: number;
    jam_mulai: string;
    jam_selesai: string;
}

interface JamPelajaranFormProps {
    jamPelajaran?: JamPelajaranRecord;
    hariList: string[];
}

export default function JamPelajaranForm({ jamPelajaran, hariList }: JamPelajaranFormProps) {
    const isEdit = Boolean(jamPelajaran);
    const [confirmOpen, setConfirmOpen] = useState(false);
    const { data, setData, post, put, processing, errors } = useForm<JamPelajaranFormData>({
        hari: jamPelajaran?.hari ?? '',
        sesi_ke: jamPelajaran?.sesi_ke?.toString() ?? '',
        jam_mulai: jamPelajaran?.jam_mulai ?? '',
        jam_selesai: jamPelajaran?.jam_selesai ?? '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();

        if (jamPelajaran) {
            put(`/jam-pelajaran/${jamPelajaran.id}`);
        } else {
            post('/jam-pelajaran');
        }
    };

    const destroy = () => {
        if (!jamPelajaran) {
            return;
        }

        router.delete(`/jam-pelajaran/${jamPelajaran.id}`, {
            onFinish: () => setConfirmOpen(false),
        });
    };

    return (
        <AppLayout title={isEdit ? 'Edit Jam Pelajaran' : 'Tambah Jam Pelajaran'}>
            <Head title={isEdit ? 'Edit Jam Pelajaran' : 'Tambah Jam Pelajaran'} />
            <div className="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <Link href="/jam-pelajaran">
                    <Button variant="outline" size="sm">
                        <ArrowLeft size={16} />
                        Kembali
                    </Button>
                </Link>
                {jamPelajaran ? (
                    <Button variant="danger" size="sm" onClick={() => setConfirmOpen(true)}>
                        <Trash2 size={16} />
                        Hapus Jam
                    </Button>
                ) : null}
            </div>

            <ResourceForm
                title={isEdit ? `Edit ${jamPelajaran?.hari} Sesi ${jamPelajaran?.sesi_ke}` : 'Tambah Jam Pelajaran'}
                onSubmit={submit}
                actions={
                    <>
                        <Link href="/jam-pelajaran">
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
                    <FormField label="Hari" error={errors.hari}>
                        <Select value={data.hari} required onChange={(event) => setData('hari', event.target.value)}>
                            <option value="">Pilih Hari</option>
                            {hariList.map((hari) => (
                                <option key={hari} value={hari}>
                                    {hari}
                                </option>
                            ))}
                        </Select>
                    </FormField>
                    <FormField label="Sesi Ke" error={errors.sesi_ke} hint="Nomor urut sesi, misal 1, 2, 3.">
                        <Input
                            type="number"
                            min={1}
                            value={data.sesi_ke}
                            required
                            onChange={(event) => setData('sesi_ke', event.target.value)}
                        />
                    </FormField>
                </div>
                <div className="grid gap-4 md:grid-cols-2">
                    <FormField label="Jam Mulai" error={errors.jam_mulai}>
                        <Input type="time" value={data.jam_mulai} required onChange={(event) => setData('jam_mulai', event.target.value)} />
                    </FormField>
                    <FormField label="Jam Selesai" error={errors.jam_selesai}>
                        <Input
                            type="time"
                            min={data.jam_mulai || undefined}
                            value={data.jam_selesai}
                            required
                            onChange={(event) => setData('jam_selesai', event.target.value)}
                        />
                    </FormField>
                </div>
                <div className="rounded-md border border-blue-200 bg-blue-50 p-4 text-sm font-medium leading-6 text-blue-900">
                    Pastikan jam mulai dan jam selesai tidak tumpang tindih dengan sesi lain pada hari yang sama.
                </div>
            </ResourceForm>

            <ConfirmDeleteDialog
                open={confirmOpen}
                message={`Hapus jadwal ${jamPelajaran?.hari ?? ''} sesi ${jamPelajaran?.sesi_ke ?? ''}? Data ini akan dihapus permanen.`}
                processing={processing}
                onClose={() => setConfirmOpen(false)}
                onConfirm={destroy}
            />
        </AppLayout>
    );
}
