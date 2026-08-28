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
import { Textarea } from '@/Components/ui/textarea';

interface KelasOption {
    id: number;
    nama_kelas: string;
}

interface TahunPelajaranOption {
    id: number;
    kode: string;
    is_aktif: boolean;
}

interface SiswaFormData {
    nis: string;
    nisn: string;
    nik: string;
    nama_lengkap: string;
    nama_orang_tua: string;
    kelas_id: string;
    tahun_pelajaran_id: string;
    jenis_kelamin: string;
    tempat_lahir: string;
    tanggal_lahir: string;
    alamat: string;
    hp: string;
    status: string;
}

interface SiswaRecord {
    id: number;
    nis: string;
    nisn: string | null;
    nik: string | null;
    nama_lengkap: string;
    nama_orang_tua: string | null;
    kelas_id: number;
    tahun_pelajaran_id: number | null;
    jenis_kelamin: 'L' | 'P' | null;
    tempat_lahir: string | null;
    tanggal_lahir: string | null;
    alamat: string | null;
    hp: string | null;
    status: string | null;
}

interface SiswaFormProps {
    siswa?: SiswaRecord;
    kelas: KelasOption[];
    tahunPelajaran: TahunPelajaranOption[];
}

const statuses = ['Aktif', 'Lulus', 'Pindah', 'Keluar'];

function onlyDigits(value: string, max: number) {
    return value.replace(/\D/g, '').slice(0, max);
}

export default function SiswaForm({ siswa, kelas, tahunPelajaran }: SiswaFormProps) {
    const isEdit = Boolean(siswa);
    const [confirmOpen, setConfirmOpen] = useState(false);
    const activeYear = tahunPelajaran.find((item) => item.is_aktif);
    const { data, setData, post, put, processing, errors } = useForm<SiswaFormData>({
        nis: siswa?.nis ?? '',
        nisn: siswa?.nisn ?? '',
        nik: siswa?.nik ?? '',
        nama_lengkap: siswa?.nama_lengkap ?? '',
        nama_orang_tua: siswa?.nama_orang_tua ?? '',
        kelas_id: siswa?.kelas_id?.toString() ?? '',
        tahun_pelajaran_id: siswa?.tahun_pelajaran_id?.toString() ?? activeYear?.id.toString() ?? '',
        jenis_kelamin: siswa?.jenis_kelamin ?? '',
        tempat_lahir: siswa?.tempat_lahir ?? '',
        tanggal_lahir: siswa?.tanggal_lahir ?? '',
        alamat: siswa?.alamat ?? '',
        hp: siswa?.hp ?? '',
        status: siswa?.status ?? 'Aktif',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();

        if (siswa) {
            put(`/siswa/${siswa.id}`);
        } else {
            post('/siswa');
        }
    };

    const destroy = () => {
        if (!siswa) {
            return;
        }

        router.delete(`/siswa/${siswa.id}`, {
            onFinish: () => setConfirmOpen(false),
        });
    };

    return (
        <AppLayout title={isEdit ? 'Edit Data Siswa' : 'Tambah Siswa Baru'}>
            <Head title={isEdit ? 'Edit Data Siswa' : 'Tambah Siswa Baru'} />
            <div className="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <Link href="/siswa">
                    <Button variant="outline" size="sm">
                        <ArrowLeft size={16} />
                        Kembali
                    </Button>
                </Link>
                {siswa ? (
                    <Button variant="danger" size="sm" onClick={() => setConfirmOpen(true)}>
                        <Trash2 size={16} />
                        Hapus Siswa
                    </Button>
                ) : null}
            </div>

            <ResourceForm
                title={isEdit ? `Edit Siswa: ${siswa?.nama_lengkap}` : 'Tambah Siswa Baru'}
                onSubmit={submit}
                actions={
                    <>
                        <Link href="/siswa">
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
                    <FormField label="Nama Lengkap" error={errors.nama_lengkap}>
                        <Input value={data.nama_lengkap} required onChange={(event) => setData('nama_lengkap', event.target.value)} />
                    </FormField>
                    <FormField label="Nama Orang Tua" error={errors.nama_orang_tua}>
                        <Input value={data.nama_orang_tua} onChange={(event) => setData('nama_orang_tua', event.target.value)} />
                    </FormField>
                </div>

                <div className="grid gap-4 md:grid-cols-3">
                    <FormField label="NIS" error={errors.nis}>
                        <Input value={data.nis} required onChange={(event) => setData('nis', event.target.value)} />
                    </FormField>
                    <FormField label="NISN" error={errors.nisn}>
                        <Input
                            value={data.nisn}
                            inputMode="numeric"
                            maxLength={10}
                            onChange={(event) => setData('nisn', onlyDigits(event.target.value, 10))}
                        />
                    </FormField>
                    <FormField label="NIK" error={errors.nik}>
                        <Input
                            value={data.nik}
                            inputMode="numeric"
                            maxLength={16}
                            onChange={(event) => setData('nik', onlyDigits(event.target.value, 16))}
                        />
                    </FormField>
                </div>

                <div className="grid gap-4 md:grid-cols-3">
                    <FormField label="Tempat Lahir" error={errors.tempat_lahir}>
                        <Input value={data.tempat_lahir} onChange={(event) => setData('tempat_lahir', event.target.value)} />
                    </FormField>
                    <FormField label="Tanggal Lahir" error={errors.tanggal_lahir}>
                        <Input type="date" value={data.tanggal_lahir} onChange={(event) => setData('tanggal_lahir', event.target.value)} />
                    </FormField>
                    <FormField label="Jenis Kelamin" error={errors.jenis_kelamin}>
                        <Select value={data.jenis_kelamin} required onChange={(event) => setData('jenis_kelamin', event.target.value)}>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </Select>
                    </FormField>
                </div>

                <div className="grid gap-4 md:grid-cols-2">
                    <FormField label="Kelas" error={errors.kelas_id}>
                        <Select value={data.kelas_id} required onChange={(event) => setData('kelas_id', event.target.value)}>
                            <option value="">Pilih Kelas</option>
                            {kelas.map((item) => (
                                <option key={item.id} value={item.id}>
                                    {item.nama_kelas}
                                </option>
                            ))}
                        </Select>
                    </FormField>
                    <FormField label="Tahun Pelajaran" error={errors.tahun_pelajaran_id}>
                        <Select value={data.tahun_pelajaran_id} onChange={(event) => setData('tahun_pelajaran_id', event.target.value)}>
                            <option value="">Pilih Tahun</option>
                            {tahunPelajaran.map((item) => (
                                <option key={item.id} value={item.id}>
                                    {item.kode}
                                </option>
                            ))}
                        </Select>
                    </FormField>
                </div>

                <div className="grid gap-4 md:grid-cols-2">
                    <FormField label="Status Siswa" error={errors.status}>
                        <Select value={data.status} required onChange={(event) => setData('status', event.target.value)}>
                            {statuses.map((item) => (
                                <option key={item} value={item}>
                                    {item}
                                </option>
                            ))}
                        </Select>
                    </FormField>
                    <FormField label="No. HP / WhatsApp" error={errors.hp}>
                        <Input value={data.hp} inputMode="tel" onChange={(event) => setData('hp', onlyDigits(event.target.value, 20))} />
                    </FormField>
                </div>

                <FormField label="Alamat Lengkap" error={errors.alamat}>
                    <Textarea value={data.alamat} onChange={(event) => setData('alamat', event.target.value)} />
                </FormField>
            </ResourceForm>

            <ConfirmDeleteDialog
                open={confirmOpen}
                message={`Hapus siswa ${siswa?.nama_lengkap ?? ''}? Data akademik terkait perlu diperiksa sebelum penghapusan.`}
                processing={processing}
                onClose={() => setConfirmOpen(false)}
                onConfirm={destroy}
            />
        </AppLayout>
    );
}
