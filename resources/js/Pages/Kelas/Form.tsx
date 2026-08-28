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

interface GuruOption {
    id: number;
    nama: string;
    kode: string;
}

interface KelasFormData {
    nama_kelas: string;
    tingkat: string;
    guru_pembimbing_id: string;
    kapasitas: string;
    ruangan: string;
    fase: string;
}

interface KelasRecord {
    id: number;
    nama_kelas: string;
    tingkat: string;
    guru_pembimbing_id: number | null;
    kapasitas: number | null;
    ruangan: string | null;
    fase: string | null;
}

interface KelasFormProps {
    kelas?: KelasRecord;
    gurus: GuruOption[];
}

export default function KelasForm({ kelas, gurus }: KelasFormProps) {
    const isEdit = Boolean(kelas);
    const [confirmOpen, setConfirmOpen] = useState(false);
    const { data, setData, post, put, processing, errors } = useForm<KelasFormData>({
        nama_kelas: kelas?.nama_kelas ?? '',
        tingkat: kelas?.tingkat ?? '',
        guru_pembimbing_id: kelas?.guru_pembimbing_id?.toString() ?? '',
        kapasitas: kelas?.kapasitas?.toString() ?? '40',
        ruangan: kelas?.ruangan ?? '',
        fase: kelas?.fase ?? '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();

        if (kelas) {
            put(`/kelas/${kelas.id}`);
        } else {
            post('/kelas');
        }
    };

    const destroy = () => {
        if (!kelas) {
            return;
        }

        router.delete(`/kelas/${kelas.id}`, {
            onFinish: () => setConfirmOpen(false),
        });
    };

    return (
        <AppLayout title={isEdit ? 'Edit Kelas' : 'Tambah Kelas'}>
            <Head title={isEdit ? 'Edit Kelas' : 'Tambah Kelas'} />
            <div className="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <Link href="/kelas">
                    <Button variant="outline" size="sm">
                        <ArrowLeft size={16} />
                        Kembali
                    </Button>
                </Link>
                {kelas ? (
                    <Button variant="danger" size="sm" onClick={() => setConfirmOpen(true)}>
                        <Trash2 size={16} />
                        Hapus Kelas
                    </Button>
                ) : null}
            </div>

            <ResourceForm
                title={isEdit ? `Edit Kelas: ${kelas?.nama_kelas}` : 'Tambah Kelas Baru'}
                onSubmit={submit}
                actions={
                    <>
                        <Link href="/kelas">
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
                    <FormField label="Nama Kelas" error={errors.nama_kelas} hint="Contoh: 7A, 8B, 9C. Harus unik.">
                        <Input value={data.nama_kelas} required onChange={(event) => setData('nama_kelas', event.target.value)} />
                    </FormField>
                    <FormField label="Tingkat" error={errors.tingkat}>
                        <Select value={data.tingkat} required onChange={(event) => setData('tingkat', event.target.value)}>
                            <option value="">Pilih Tingkat</option>
                            <option value="7">7 (Kelas 7)</option>
                            <option value="8">8 (Kelas 8)</option>
                            <option value="9">9 (Kelas 9)</option>
                        </Select>
                    </FormField>
                </div>
                <div className="grid gap-4 md:grid-cols-2">
                    <FormField label="Wali Kelas" error={errors.guru_pembimbing_id}>
                        <Select value={data.guru_pembimbing_id} onChange={(event) => setData('guru_pembimbing_id', event.target.value)}>
                            <option value="">Pilih Wali Kelas</option>
                            {gurus.map((guru) => (
                                <option key={guru.id} value={guru.id}>
                                    {guru.nama} ({guru.kode})
                                </option>
                            ))}
                        </Select>
                    </FormField>
                    <FormField label="Kapasitas" error={errors.kapasitas}>
                        <Input
                            type="number"
                            min={1}
                            value={data.kapasitas}
                            onChange={(event) => setData('kapasitas', event.target.value)}
                        />
                    </FormField>
                </div>
                <div className="grid gap-4 md:grid-cols-2">
                    <FormField label="Ruangan" error={errors.ruangan}>
                        <Input value={data.ruangan} onChange={(event) => setData('ruangan', event.target.value)} />
                    </FormField>
                    <FormField label="Fase" error={errors.fase} hint="Opsional, misalnya D untuk MTs kelas 7-9.">
                        <Input value={data.fase} maxLength={5} onChange={(event) => setData('fase', event.target.value.toUpperCase())} />
                    </FormField>
                </div>
            </ResourceForm>

            <ConfirmDeleteDialog
                open={confirmOpen}
                message={`Hapus kelas ${kelas?.nama_kelas ?? ''}? Sistem akan menolak jika masih ada jadwal terkait.`}
                processing={processing}
                onClose={() => setConfirmOpen(false)}
                onConfirm={destroy}
            />
        </AppLayout>
    );
}
