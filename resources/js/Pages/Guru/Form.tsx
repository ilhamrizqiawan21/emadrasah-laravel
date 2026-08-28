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

interface GuruFormData {
    kode: string;
    nama: string;
    bidang_studi: string;
    nip: string;
    email: string;
    phone: string;
    beban_jp: string;
}

interface GuruRecord {
    id: number;
    kode: string;
    nama: string;
    bidang_studi: string | null;
    nip: string | null;
    email: string | null;
    phone: string | null;
    beban_jp: number | null;
}

interface GuruFormProps {
    guru?: GuruRecord;
    mapels: string[];
}

export default function GuruForm({ guru, mapels }: GuruFormProps) {
    const isEdit = Boolean(guru);
    const [confirmOpen, setConfirmOpen] = useState(false);
    const { data, setData, post, put, processing, errors } = useForm<GuruFormData>({
        kode: guru?.kode ?? '',
        nama: guru?.nama ?? '',
        bidang_studi: guru?.bidang_studi ?? '',
        nip: guru?.nip ?? '',
        email: guru?.email ?? '',
        phone: guru?.phone ?? '',
        beban_jp: guru?.beban_jp?.toString() ?? '24',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();

        if (guru) {
            put(`/guru/${guru.id}`);
        } else {
            post('/guru');
        }
    };

    const destroy = () => {
        if (!guru) {
            return;
        }

        router.delete(`/guru/${guru.id}`, {
            onFinish: () => setConfirmOpen(false),
        });
    };

    return (
        <AppLayout title={isEdit ? 'Edit Guru' : 'Tambah Guru'}>
            <Head title={isEdit ? 'Edit Guru' : 'Tambah Guru'} />
            <div className="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <Link href="/guru">
                    <Button variant="outline" size="sm">
                        <ArrowLeft size={16} />
                        Kembali
                    </Button>
                </Link>
                {guru ? (
                    <Button variant="danger" size="sm" onClick={() => setConfirmOpen(true)}>
                        <Trash2 size={16} />
                        Hapus Guru
                    </Button>
                ) : null}
            </div>

            <ResourceForm
                title={isEdit ? `Edit Guru: ${guru?.nama}` : 'Tambah Guru Baru'}
                onSubmit={submit}
                actions={
                    <>
                        <Link href="/guru">
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
                    <FormField label="Kode Guru" error={errors.kode} hint="Contoh: 1, 4A, 3/15A. Harus unik.">
                        <Input value={data.kode} required onChange={(event) => setData('kode', event.target.value)} />
                    </FormField>
                    <FormField label="Nama Lengkap" error={errors.nama}>
                        <Input value={data.nama} required onChange={(event) => setData('nama', event.target.value)} />
                    </FormField>
                </div>
                <div className="grid gap-4 md:grid-cols-2">
                    <FormField label="NIP" error={errors.nip}>
                        <Input value={data.nip} onChange={(event) => setData('nip', event.target.value.replace(/\D/g, '').slice(0, 30))} />
                    </FormField>
                    <FormField label="Email" error={errors.email}>
                        <Input type="email" value={data.email} onChange={(event) => setData('email', event.target.value)} />
                    </FormField>
                </div>
                <div className="grid gap-4 md:grid-cols-2">
                    <FormField label="No. Telepon" error={errors.phone}>
                        <Input
                            value={data.phone}
                            inputMode="tel"
                            onChange={(event) => setData('phone', event.target.value.replace(/\D/g, '').slice(0, 15))}
                        />
                    </FormField>
                    <FormField label="Beban JP" error={errors.beban_jp}>
                        <Input
                            type="number"
                            min={0}
                            value={data.beban_jp}
                            onChange={(event) => setData('beban_jp', event.target.value)}
                        />
                    </FormField>
                </div>
                <FormField label="Bidang Studi" error={errors.bidang_studi} hint="Opsional, dipakai untuk rekomendasi mapel.">
                    <Select value={data.bidang_studi} onChange={(event) => setData('bidang_studi', event.target.value)}>
                        <option value="">Pilih Mata Pelajaran</option>
                        {mapels.map((mapel) => (
                            <option key={mapel} value={mapel}>
                                {mapel}
                            </option>
                        ))}
                    </Select>
                </FormField>
            </ResourceForm>

            <ConfirmDeleteDialog
                open={confirmOpen}
                message={`Hapus guru ${guru?.nama ?? ''}? Sistem akan menolak jika masih ada jadwal atau riwayat absensi.`}
                processing={processing}
                onClose={() => setConfirmOpen(false)}
                onConfirm={destroy}
            />
        </AppLayout>
    );
}
