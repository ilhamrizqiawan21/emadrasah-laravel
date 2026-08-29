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

type KondisiSarana = 'baik' | 'rusak_ringan' | 'rusak_berat' | 'hilang';

interface KategoriOption {
    id: number;
    nama_kategori: string;
}

interface SaranaRecord {
    id: number;
    kode_sarana: string;
    nama_sarana: string;
    kategori_id: number | null;
    spesifikasi: string | null;
    jumlah: number;
    stok_tersedia: number;
    kondisi: KondisiSarana;
    lokasi_ruang: string | null;
    tahun_pengadaan: string | null;
    foto_url: string | null;
}

interface SaranaFormData {
    kode_sarana: string;
    nama_sarana: string;
    kategori_id: string;
    spesifikasi: string;
    jumlah: string;
    stok_tersedia: string;
    kondisi: KondisiSarana;
    lokasi_ruang: string;
    tahun_pengadaan: string;
    foto: File | null;
}

interface SaranaFormProps {
    sarana?: SaranaRecord;
    kategori: KategoriOption[];
}

export default function SaranaForm({ sarana, kategori }: SaranaFormProps) {
    const isEdit = Boolean(sarana);
    const [confirmOpen, setConfirmOpen] = useState(false);
    const { data, setData, post, processing, errors, transform } = useForm<SaranaFormData>({
        kode_sarana: sarana?.kode_sarana ?? '',
        nama_sarana: sarana?.nama_sarana ?? '',
        kategori_id: sarana?.kategori_id?.toString() ?? '',
        spesifikasi: sarana?.spesifikasi ?? '',
        jumlah: sarana?.jumlah?.toString() ?? '1',
        stok_tersedia: sarana?.stok_tersedia?.toString() ?? '',
        kondisi: sarana?.kondisi ?? 'baik',
        lokasi_ruang: sarana?.lokasi_ruang ?? '',
        tahun_pengadaan: sarana?.tahun_pengadaan ?? '',
        foto: null,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();

        if (sarana) {
            transform((data) => ({ ...data, _method: 'PUT' }));
            post(`/sarana/${sarana.id}`, { forceFormData: true });
        } else {
            transform((data) => data);
            post('/sarana', { forceFormData: true });
        }
    };

    const destroy = () => {
        if (!sarana) {
            return;
        }

        router.delete(`/sarana/${sarana.id}`, {
            onFinish: () => setConfirmOpen(false),
        });
    };

    return (
        <AppLayout title={isEdit ? 'Edit Sarana' : 'Tambah Sarana'}>
            <Head title={isEdit ? 'Edit Sarana' : 'Tambah Sarana'} />
            <div className="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <Link href="/sarana">
                    <Button variant="outline" size="sm">
                        <ArrowLeft size={16} />
                        Kembali
                    </Button>
                </Link>
                {sarana ? (
                    <Button variant="danger" size="sm" onClick={() => setConfirmOpen(true)}>
                        <Trash2 size={16} />
                        Hapus Sarana
                    </Button>
                ) : null}
            </div>

            <ResourceForm
                title={isEdit ? `Edit Sarana: ${sarana?.nama_sarana}` : 'Tambah Sarana Prasarana'}
                onSubmit={submit}
                actions={
                    <>
                        <Link href="/sarana">
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
                    <FormField label="Kode Sarana" error={errors.kode_sarana} hint="Contoh: PR-001, LAP-01. Harus unik.">
                        <Input value={data.kode_sarana} required onChange={(event) => setData('kode_sarana', event.target.value)} />
                    </FormField>
                    <FormField label="Nama Sarana" error={errors.nama_sarana}>
                        <Input value={data.nama_sarana} required onChange={(event) => setData('nama_sarana', event.target.value)} />
                    </FormField>
                </div>
                <div className="grid gap-4 md:grid-cols-2">
                    <FormField label="Kategori" error={errors.kategori_id}>
                        <Select value={data.kategori_id} required onChange={(event) => setData('kategori_id', event.target.value)}>
                            <option value="">Pilih Kategori</option>
                            {kategori.map((item) => <option key={item.id} value={item.id}>{item.nama_kategori}</option>)}
                        </Select>
                    </FormField>
                    <FormField label="Kondisi" error={errors.kondisi}>
                        <Select value={data.kondisi} required onChange={(event) => setData('kondisi', event.target.value as KondisiSarana)}>
                            <option value="baik">Baik</option>
                            <option value="rusak_ringan">Rusak Ringan</option>
                            <option value="rusak_berat">Rusak Berat</option>
                            <option value="hilang">Hilang</option>
                        </Select>
                    </FormField>
                </div>
                <div className="grid gap-4 md:grid-cols-3">
                    <FormField label="Jumlah" error={errors.jumlah}>
                        <Input type="number" min={1} value={data.jumlah} required onChange={(event) => setData('jumlah', event.target.value)} />
                    </FormField>
                    <FormField label="Stok Tersedia" error={errors.stok_tersedia} hint="Kosongkan saat tambah untuk mengikuti jumlah.">
                        <Input type="number" min={0} value={data.stok_tersedia} onChange={(event) => setData('stok_tersedia', event.target.value)} />
                    </FormField>
                    <FormField label="Tahun Pengadaan" error={errors.tahun_pengadaan}>
                        <Input type="number" min={1900} max={new Date().getFullYear() + 1} value={data.tahun_pengadaan} onChange={(event) => setData('tahun_pengadaan', event.target.value)} />
                    </FormField>
                </div>
                <FormField label="Lokasi Ruang" error={errors.lokasi_ruang}>
                    <Input value={data.lokasi_ruang} placeholder="Contoh: Lab IPA, Ruang Guru, Aula" onChange={(event) => setData('lokasi_ruang', event.target.value)} />
                </FormField>
                <FormField label="Spesifikasi" error={errors.spesifikasi}>
                    <Textarea value={data.spesifikasi} rows={3} onChange={(event) => setData('spesifikasi', event.target.value)} />
                </FormField>
                {sarana?.foto_url ? (
                    <a href={sarana.foto_url} target="_blank" rel="noreferrer" className="text-sm font-semibold text-blue-700">
                        Lihat foto saat ini
                    </a>
                ) : null}
                <FileUploadField label="Foto" name="foto" accept="image/*" hint="Gambar maksimal 2MB. Kosongkan jika tidak ingin mengubah foto." error={errors.foto} onChange={(file) => setData('foto', file)} />
            </ResourceForm>

            <ConfirmDeleteDialog
                open={confirmOpen}
                message={`Hapus sarana ${sarana?.nama_sarana ?? ''}?`}
                processing={processing}
                onClose={() => setConfirmOpen(false)}
                onConfirm={destroy}
            />
        </AppLayout>
    );
}
