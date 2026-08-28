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
import { Tabs } from '@/Components/ui/tabs';
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

type FormValue = string | File | null;

interface BukuIndukFormData {
    _method: string;
    no_urut: string;
    nis: string;
    nisn: string;
    nism: string;
    nik: string;
    no_kk: string;
    nama_lengkap: string;
    nama_panggilan: string;
    jenis_kelamin: string;
    tempat_lahir: string;
    tanggal_lahir: string;
    agama: string;
    kewarganegaraan: string;
    anak_ke: string;
    saudara_kandung: string;
    saudara_tiri: string;
    saudara_angkat: string;
    status_anak: string;
    yatim_piatu: string;
    bahasa_sehari_hari: string;
    alamat: string;
    rt: string;
    rw: string;
    desa_kelurahan: string;
    kecamatan: string;
    kabupaten_kota: string;
    provinsi: string;
    kode_pos: string;
    nama_orang_tua: string;
    no_telepon: string;
    hp: string;
    bertempat_tinggal_pada: string;
    jarak_ke_madrasah: string;
    moda_transportasi: string;
    golongan_darah: string;
    penyakit_pernah_diderita: string;
    kelainan_jasmani: string;
    tinggi_badan_awal: string;
    berat_badan_awal: string;
    hobi_kesenian: string;
    hobi_olahraga: string;
    hobi_organisasi: string;
    hobi_lain: string;
    kelas_id: string;
    tahun_pelajaran_id: string;
    status: string;
    nama_ayah: string;
    pendidikan_ayah: string;
    pekerjaan_ayah: string;
    penghasilan_ayah: string;
    no_hp_ayah: string;
    status_ayah: string;
    nama_ibu: string;
    pendidikan_ibu: string;
    pekerjaan_ibu: string;
    penghasilan_ibu: string;
    no_hp_ibu: string;
    status_ibu: string;
    nama_wali: string;
    hubungan_wali: string;
    pendidikan_wali: string;
    pekerjaan_wali: string;
    no_hp_wali: string;
    alamat_ortu: string;
    asal_madrasah: string;
    nama_madrasah_asal: string;
    tgl_ijazah_asal: string;
    no_ijazah_asal: string;
    jenis_masuk: string;
    tgl_diterima: string;
    dari_tingkat: string;
    no_surat_pindah: string;
    jenis_keluar: string;
    thn_lulus: string;
    no_ijazah_lulus: string;
    melanjutkan_ke: string;
    pindah_ke_madrasah: string;
    pindah_tingkat: string;
    alasan_keluar: string;
    tgl_keluar: string;
    foto: File | null;
    dokumen_akta: File | null;
    dokumen_kk: File | null;
    dokumen_ijazah: File | null;
}

interface SiswaRecord {
    id: number;
    foto_url: string | null;
    orang_tua_wali: Record<string, string | null> | null;
    perkembangan: Record<string, string | null>;
    [key: string]: string | number | null | Record<string, string | null> | Array<unknown>;
}

interface BukuIndukFormProps {
    siswa?: SiswaRecord;
    kelas: KelasOption[];
    tahunPelajaran: TahunPelajaranOption[];
}

const tabs = [
    { value: 'diri', label: 'Keterangan Diri' },
    { value: 'tinggal', label: 'Tempat Tinggal' },
    { value: 'ortu', label: 'Orang Tua/Wali' },
    { value: 'pendidikan', label: 'Pendidikan' },
    { value: 'lain', label: 'Lain-lain' },
    { value: 'dokumen', label: 'Dokumen' },
];

const statuses = ['Aktif', 'Lulus', 'Pindah', 'Keluar', 'Meninggal'];

function text(value: unknown, fallback = '') {
    if (value === null || value === undefined) {
        return fallback;
    }

    return String(value);
}

function onlyDigits(value: string, max: number) {
    return value.replace(/\D/g, '').slice(0, max);
}

function setNumeric(setData: (key: keyof BukuIndukFormData, value: FormValue) => void, key: keyof BukuIndukFormData, value: string, max: number) {
    setData(key, onlyDigits(value, max));
}

export default function BukuIndukForm({ siswa, kelas, tahunPelajaran }: BukuIndukFormProps) {
    const isEdit = Boolean(siswa);
    const [activeTab, setActiveTab] = useState('diri');
    const [confirmOpen, setConfirmOpen] = useState(false);
    const activeYear = tahunPelajaran.find((item) => item.is_aktif);
    const ortu = siswa?.orang_tua_wali ?? {};
    const perkembangan = siswa?.perkembangan ?? {};

    const { data, setData, post, processing, errors } = useForm<BukuIndukFormData>({
        _method: isEdit ? 'put' : '',
        no_urut: text(siswa?.no_urut),
        nis: text(siswa?.nis),
        nisn: text(siswa?.nisn),
        nism: text(siswa?.nism),
        nik: text(siswa?.nik),
        no_kk: text(siswa?.no_kk),
        nama_lengkap: text(siswa?.nama_lengkap),
        nama_panggilan: text(siswa?.nama_panggilan),
        jenis_kelamin: text(siswa?.jenis_kelamin, 'L'),
        tempat_lahir: text(siswa?.tempat_lahir),
        tanggal_lahir: text(siswa?.tanggal_lahir),
        agama: text(siswa?.agama, 'Islam'),
        kewarganegaraan: text(siswa?.kewarganegaraan, 'Indonesia'),
        anak_ke: text(siswa?.anak_ke),
        saudara_kandung: text(siswa?.saudara_kandung, '0'),
        saudara_tiri: text(siswa?.saudara_tiri, '0'),
        saudara_angkat: text(siswa?.saudara_angkat, '0'),
        status_anak: text(siswa?.status_anak, 'Kandung'),
        yatim_piatu: text(siswa?.yatim_piatu, 'Tidak'),
        bahasa_sehari_hari: text(siswa?.bahasa_sehari_hari),
        alamat: text(siswa?.alamat),
        rt: text(siswa?.rt),
        rw: text(siswa?.rw),
        desa_kelurahan: text(siswa?.desa_kelurahan),
        kecamatan: text(siswa?.kecamatan),
        kabupaten_kota: text(siswa?.kabupaten_kota),
        provinsi: text(siswa?.provinsi),
        kode_pos: text(siswa?.kode_pos),
        nama_orang_tua: text(siswa?.nama_orang_tua),
        no_telepon: text(siswa?.no_telepon),
        hp: text(siswa?.hp),
        bertempat_tinggal_pada: text(siswa?.bertempat_tinggal_pada),
        jarak_ke_madrasah: text(siswa?.jarak_ke_madrasah),
        moda_transportasi: text(siswa?.moda_transportasi),
        golongan_darah: text(siswa?.golongan_darah, 'Tidak Tahu'),
        penyakit_pernah_diderita: text(siswa?.penyakit_pernah_diderita),
        kelainan_jasmani: text(siswa?.kelainan_jasmani),
        tinggi_badan_awal: text(siswa?.tinggi_badan_awal),
        berat_badan_awal: text(siswa?.berat_badan_awal),
        hobi_kesenian: text(siswa?.hobi_kesenian),
        hobi_olahraga: text(siswa?.hobi_olahraga),
        hobi_organisasi: text(siswa?.hobi_organisasi),
        hobi_lain: text(siswa?.hobi_lain),
        kelas_id: text(siswa?.kelas_id),
        tahun_pelajaran_id: text(siswa?.tahun_pelajaran_id, activeYear?.id.toString() ?? ''),
        status: text(siswa?.status, 'Aktif'),
        nama_ayah: text(ortu.nama_ayah),
        pendidikan_ayah: text(ortu.pendidikan_ayah),
        pekerjaan_ayah: text(ortu.pekerjaan_ayah),
        penghasilan_ayah: text(ortu.penghasilan_ayah),
        no_hp_ayah: text(ortu.no_hp_ayah),
        status_ayah: text(ortu.status_ayah, 'Hidup'),
        nama_ibu: text(ortu.nama_ibu),
        pendidikan_ibu: text(ortu.pendidikan_ibu),
        pekerjaan_ibu: text(ortu.pekerjaan_ibu),
        penghasilan_ibu: text(ortu.penghasilan_ibu),
        no_hp_ibu: text(ortu.no_hp_ibu),
        status_ibu: text(ortu.status_ibu, 'Hidup'),
        nama_wali: text(ortu.nama_wali),
        hubungan_wali: text(ortu.hubungan_wali),
        pendidikan_wali: text(ortu.pendidikan_wali),
        pekerjaan_wali: text(ortu.pekerjaan_wali),
        no_hp_wali: text(ortu.no_hp_wali),
        alamat_ortu: text(ortu.alamat_ortu),
        asal_madrasah: text(perkembangan.asal_madrasah),
        nama_madrasah_asal: text(perkembangan.nama_madrasah_asal),
        tgl_ijazah_asal: text(perkembangan.tgl_ijazah_asal),
        no_ijazah_asal: text(perkembangan.no_ijazah_asal),
        jenis_masuk: text(perkembangan.jenis_masuk, 'Baru'),
        tgl_diterima: text(perkembangan.tgl_diterima),
        dari_tingkat: text(perkembangan.dari_tingkat),
        no_surat_pindah: text(perkembangan.no_surat_pindah),
        jenis_keluar: text(perkembangan.jenis_keluar),
        thn_lulus: text(perkembangan.thn_lulus),
        no_ijazah_lulus: text(perkembangan.no_ijazah_lulus),
        melanjutkan_ke: text(perkembangan.melanjutkan_ke),
        pindah_ke_madrasah: text(perkembangan.pindah_ke_madrasah),
        pindah_tingkat: text(perkembangan.pindah_tingkat),
        alasan_keluar: text(perkembangan.alasan_keluar),
        tgl_keluar: text(perkembangan.tgl_keluar),
        foto: null,
        dokumen_akta: null,
        dokumen_kk: null,
        dokumen_ijazah: null,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        post(isEdit ? `/buku-induk/${siswa?.id}` : '/buku-induk', { forceFormData: true });
    };

    const destroy = () => {
        if (!siswa) {
            return;
        }

        router.delete(`/buku-induk/${siswa.id}`, {
            onFinish: () => setConfirmOpen(false),
        });
    };

    return (
        <AppLayout title={isEdit ? 'Edit Data Buku Induk' : 'Tambah Data Buku Induk'}>
            <Head title={isEdit ? 'Edit Data Buku Induk' : 'Tambah Data Buku Induk'} />
            <div className="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <Link href="/buku-induk">
                    <Button variant="outline" size="sm">
                        <ArrowLeft size={16} />
                        Kembali
                    </Button>
                </Link>
                {siswa ? (
                    <Button variant="danger" size="sm" onClick={() => setConfirmOpen(true)}>
                        <Trash2 size={16} />
                        Hapus Data
                    </Button>
                ) : null}
            </div>

            <div className="mb-4">
                <Tabs tabs={tabs} value={activeTab} onValueChange={setActiveTab} />
            </div>

            <ResourceForm
                title={isEdit ? `Edit Buku Induk: ${siswa?.nama_lengkap}` : 'Tambah Data Buku Induk'}
                onSubmit={submit}
                actions={
                    <>
                        <Link href="/buku-induk">
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

                {activeTab === 'diri' ? (
                    <>
                        <div className="grid gap-4 md:grid-cols-[140px,1fr,1fr]">
                            <FormField label="No. Urut" error={errors.no_urut}>
                                <Input type="number" min={1} value={data.no_urut} onChange={(event) => setData('no_urut', event.target.value)} />
                            </FormField>
                            <FormField label="Nama Lengkap" error={errors.nama_lengkap}>
                                <Input value={data.nama_lengkap} required onChange={(event) => setData('nama_lengkap', event.target.value)} />
                            </FormField>
                            <FormField label="Nama Panggilan" error={errors.nama_panggilan}>
                                <Input value={data.nama_panggilan} onChange={(event) => setData('nama_panggilan', event.target.value)} />
                            </FormField>
                        </div>
                        <div className="grid gap-4 md:grid-cols-4">
                            <FormField label="NIS" error={errors.nis}>
                                <Input value={data.nis} required onChange={(event) => setData('nis', event.target.value)} />
                            </FormField>
                            <FormField label="NISN" error={errors.nisn}>
                                <Input value={data.nisn} inputMode="numeric" maxLength={10} onChange={(event) => setNumeric(setData, 'nisn', event.target.value, 10)} />
                            </FormField>
                            <FormField label="NISM" error={errors.nism}>
                                <Input value={data.nism} onChange={(event) => setData('nism', event.target.value)} />
                            </FormField>
                            <FormField label="NIK" error={errors.nik}>
                                <Input value={data.nik} inputMode="numeric" maxLength={16} onChange={(event) => setNumeric(setData, 'nik', event.target.value, 16)} />
                            </FormField>
                        </div>
                        <div className="grid gap-4 md:grid-cols-4">
                            <FormField label="Jenis Kelamin" error={errors.jenis_kelamin}>
                                <Select value={data.jenis_kelamin} onChange={(event) => setData('jenis_kelamin', event.target.value)}>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </Select>
                            </FormField>
                            <FormField label="Tempat Lahir" error={errors.tempat_lahir}>
                                <Input value={data.tempat_lahir} onChange={(event) => setData('tempat_lahir', event.target.value)} />
                            </FormField>
                            <FormField label="Tanggal Lahir" error={errors.tanggal_lahir}>
                                <Input type="date" value={data.tanggal_lahir} onChange={(event) => setData('tanggal_lahir', event.target.value)} />
                            </FormField>
                            <FormField label="Agama" error={errors.agama}>
                                <Input value={data.agama} onChange={(event) => setData('agama', event.target.value)} />
                            </FormField>
                        </div>
                        <div className="grid gap-4 md:grid-cols-4">
                            <FormField label="No. KK" error={errors.no_kk}>
                                <Input value={data.no_kk} inputMode="numeric" maxLength={16} onChange={(event) => setNumeric(setData, 'no_kk', event.target.value, 16)} />
                            </FormField>
                            <FormField label="Kewarganegaraan" error={errors.kewarganegaraan}>
                                <Input value={data.kewarganegaraan} onChange={(event) => setData('kewarganegaraan', event.target.value)} />
                            </FormField>
                            <FormField label="Anak Ke" error={errors.anak_ke}>
                                <Input type="number" min={1} value={data.anak_ke} onChange={(event) => setData('anak_ke', event.target.value)} />
                            </FormField>
                            <FormField label="Golongan Darah" error={errors.golongan_darah}>
                                <Select value={data.golongan_darah} onChange={(event) => setData('golongan_darah', event.target.value)}>
                                    {['Tidak Tahu', 'A', 'B', 'AB', 'O'].map((item) => <option key={item} value={item}>{item}</option>)}
                                </Select>
                            </FormField>
                        </div>
                    </>
                ) : null}

                {activeTab === 'tinggal' ? (
                    <>
                        <FormField label="Alamat Jalan" error={errors.alamat}>
                            <Textarea value={data.alamat} onChange={(event) => setData('alamat', event.target.value)} />
                        </FormField>
                        <div className="grid gap-4 md:grid-cols-4">
                            <FormField label="RT" error={errors.rt}>
                                <Input value={data.rt} maxLength={5} onChange={(event) => setData('rt', event.target.value)} />
                            </FormField>
                            <FormField label="RW" error={errors.rw}>
                                <Input value={data.rw} maxLength={5} onChange={(event) => setData('rw', event.target.value)} />
                            </FormField>
                            <FormField label="Desa / Kelurahan" error={errors.desa_kelurahan}>
                                <Input value={data.desa_kelurahan} onChange={(event) => setData('desa_kelurahan', event.target.value)} />
                            </FormField>
                            <FormField label="Kecamatan" error={errors.kecamatan}>
                                <Input value={data.kecamatan} onChange={(event) => setData('kecamatan', event.target.value)} />
                            </FormField>
                        </div>
                        <div className="grid gap-4 md:grid-cols-4">
                            <FormField label="Kabupaten / Kota" error={errors.kabupaten_kota}>
                                <Input value={data.kabupaten_kota} onChange={(event) => setData('kabupaten_kota', event.target.value)} />
                            </FormField>
                            <FormField label="Provinsi" error={errors.provinsi}>
                                <Input value={data.provinsi} onChange={(event) => setData('provinsi', event.target.value)} />
                            </FormField>
                            <FormField label="Kode Pos" error={errors.kode_pos}>
                                <Input value={data.kode_pos} inputMode="numeric" maxLength={10} onChange={(event) => setNumeric(setData, 'kode_pos', event.target.value, 10)} />
                            </FormField>
                            <FormField label="No. HP" error={errors.hp}>
                                <Input value={data.hp} inputMode="tel" onChange={(event) => setNumeric(setData, 'hp', event.target.value, 20)} />
                            </FormField>
                        </div>
                    </>
                ) : null}

                {activeTab === 'ortu' ? (
                    <>
                        <div className="grid gap-4 md:grid-cols-3">
                            <FormField label="Nama Ayah" error={errors.nama_ayah}>
                                <Input value={data.nama_ayah} onChange={(event) => setData('nama_ayah', event.target.value)} />
                            </FormField>
                            <FormField label="Pendidikan Ayah" error={errors.pendidikan_ayah}>
                                <Input value={data.pendidikan_ayah} onChange={(event) => setData('pendidikan_ayah', event.target.value)} />
                            </FormField>
                            <FormField label="Pekerjaan Ayah" error={errors.pekerjaan_ayah}>
                                <Input value={data.pekerjaan_ayah} onChange={(event) => setData('pekerjaan_ayah', event.target.value)} />
                            </FormField>
                        </div>
                        <div className="grid gap-4 md:grid-cols-3">
                            <FormField label="Nama Ibu" error={errors.nama_ibu}>
                                <Input value={data.nama_ibu} onChange={(event) => setData('nama_ibu', event.target.value)} />
                            </FormField>
                            <FormField label="Pendidikan Ibu" error={errors.pendidikan_ibu}>
                                <Input value={data.pendidikan_ibu} onChange={(event) => setData('pendidikan_ibu', event.target.value)} />
                            </FormField>
                            <FormField label="Pekerjaan Ibu" error={errors.pekerjaan_ibu}>
                                <Input value={data.pekerjaan_ibu} onChange={(event) => setData('pekerjaan_ibu', event.target.value)} />
                            </FormField>
                        </div>
                        <div className="grid gap-4 md:grid-cols-3">
                            <FormField label="Nama Wali" error={errors.nama_wali}>
                                <Input value={data.nama_wali} onChange={(event) => setData('nama_wali', event.target.value)} />
                            </FormField>
                            <FormField label="Hubungan Wali" error={errors.hubungan_wali}>
                                <Input value={data.hubungan_wali} onChange={(event) => setData('hubungan_wali', event.target.value)} />
                            </FormField>
                            <FormField label="No. HP Wali" error={errors.no_hp_wali}>
                                <Input value={data.no_hp_wali} inputMode="tel" onChange={(event) => setNumeric(setData, 'no_hp_wali', event.target.value, 20)} />
                            </FormField>
                        </div>
                        <FormField label="Alamat Orang Tua / Wali" error={errors.alamat_ortu}>
                            <Textarea value={data.alamat_ortu} onChange={(event) => setData('alamat_ortu', event.target.value)} />
                        </FormField>
                    </>
                ) : null}

                {activeTab === 'pendidikan' ? (
                    <>
                        <div className="grid gap-4 md:grid-cols-3">
                            <FormField label="Asal Madrasah" error={errors.asal_madrasah}>
                                <Input value={data.asal_madrasah} onChange={(event) => setData('asal_madrasah', event.target.value)} />
                            </FormField>
                            <FormField label="Nama Madrasah Asal" error={errors.nama_madrasah_asal}>
                                <Input value={data.nama_madrasah_asal} onChange={(event) => setData('nama_madrasah_asal', event.target.value)} />
                            </FormField>
                            <FormField label="No. Ijazah Asal" error={errors.no_ijazah_asal}>
                                <Input value={data.no_ijazah_asal} onChange={(event) => setData('no_ijazah_asal', event.target.value)} />
                            </FormField>
                        </div>
                        <div className="grid gap-4 md:grid-cols-4">
                            <FormField label="Jenis Masuk" error={errors.jenis_masuk}>
                                <Select value={data.jenis_masuk} onChange={(event) => setData('jenis_masuk', event.target.value)}>
                                    <option value="Baru">Baru</option>
                                    <option value="Pindahan">Pindahan</option>
                                </Select>
                            </FormField>
                            <FormField label="Tanggal Diterima" error={errors.tgl_diterima}>
                                <Input type="date" value={data.tgl_diterima} onChange={(event) => setData('tgl_diterima', event.target.value)} />
                            </FormField>
                            <FormField label="Diterima di Kelas" error={errors.kelas_id}>
                                <Select value={data.kelas_id} required onChange={(event) => setData('kelas_id', event.target.value)}>
                                    <option value="">Pilih Kelas</option>
                                    {kelas.map((item) => <option key={item.id} value={item.id}>{item.nama_kelas}</option>)}
                                </Select>
                            </FormField>
                            <FormField label="Tahun Pelajaran" error={errors.tahun_pelajaran_id}>
                                <Select value={data.tahun_pelajaran_id} onChange={(event) => setData('tahun_pelajaran_id', event.target.value)}>
                                    <option value="">Pilih Tahun</option>
                                    {tahunPelajaran.map((item) => <option key={item.id} value={item.id}>{item.kode}</option>)}
                                </Select>
                            </FormField>
                        </div>
                        <div className="grid gap-4 md:grid-cols-3">
                            <FormField label="No. Surat Pindah" error={errors.no_surat_pindah}>
                                <Input value={data.no_surat_pindah} onChange={(event) => setData('no_surat_pindah', event.target.value)} />
                            </FormField>
                            <FormField label="Tahun Lulus" error={errors.thn_lulus}>
                                <Input value={data.thn_lulus} maxLength={9} onChange={(event) => setData('thn_lulus', event.target.value)} />
                            </FormField>
                            <FormField label="Melanjutkan Ke" error={errors.melanjutkan_ke}>
                                <Input value={data.melanjutkan_ke} onChange={(event) => setData('melanjutkan_ke', event.target.value)} />
                            </FormField>
                        </div>
                    </>
                ) : null}

                {activeTab === 'lain' ? (
                    <>
                        <div className="grid gap-4 md:grid-cols-3">
                            <FormField label="Status Siswa" error={errors.status}>
                                <Select value={data.status} required onChange={(event) => setData('status', event.target.value)}>
                                    {statuses.map((item) => <option key={item} value={item}>{item}</option>)}
                                </Select>
                            </FormField>
                            <FormField label="Status Anak" error={errors.status_anak}>
                                <Select value={data.status_anak} onChange={(event) => setData('status_anak', event.target.value)}>
                                    {['Kandung', 'Tiri', 'Angkat'].map((item) => <option key={item} value={item}>{item}</option>)}
                                </Select>
                            </FormField>
                            <FormField label="Yatim / Piatu" error={errors.yatim_piatu}>
                                <Select value={data.yatim_piatu} onChange={(event) => setData('yatim_piatu', event.target.value)}>
                                    {['Tidak', 'Yatim', 'Piatu', 'Yatim Piatu'].map((item) => <option key={item} value={item}>{item}</option>)}
                                </Select>
                            </FormField>
                        </div>
                        <div className="grid gap-4 md:grid-cols-4">
                            <FormField label="Hobi Kesenian" error={errors.hobi_kesenian}>
                                <Input value={data.hobi_kesenian} onChange={(event) => setData('hobi_kesenian', event.target.value)} />
                            </FormField>
                            <FormField label="Hobi Olahraga" error={errors.hobi_olahraga}>
                                <Input value={data.hobi_olahraga} onChange={(event) => setData('hobi_olahraga', event.target.value)} />
                            </FormField>
                            <FormField label="Hobi Organisasi" error={errors.hobi_organisasi}>
                                <Input value={data.hobi_organisasi} onChange={(event) => setData('hobi_organisasi', event.target.value)} />
                            </FormField>
                            <FormField label="Hobi Lain" error={errors.hobi_lain}>
                                <Input value={data.hobi_lain} onChange={(event) => setData('hobi_lain', event.target.value)} />
                            </FormField>
                        </div>
                        <div className="grid gap-4 md:grid-cols-2">
                            <FormField label="Penyakit Pernah Diderita" error={errors.penyakit_pernah_diderita}>
                                <Textarea value={data.penyakit_pernah_diderita} onChange={(event) => setData('penyakit_pernah_diderita', event.target.value)} />
                            </FormField>
                            <FormField label="Kelainan Jasmani" error={errors.kelainan_jasmani}>
                                <Textarea value={data.kelainan_jasmani} onChange={(event) => setData('kelainan_jasmani', event.target.value)} />
                            </FormField>
                        </div>
                    </>
                ) : null}

                {activeTab === 'dokumen' ? (
                    <>
                        {siswa?.foto_url ? <img src={siswa.foto_url} alt={text(siswa.nama_lengkap)} className="h-36 w-28 rounded-md object-cover" /> : null}
                        <div className="grid gap-4 md:grid-cols-2">
                            <FileUploadField label="Foto Siswa" name="foto" accept="image/*" error={errors.foto} onChange={(file) => setData('foto', file)} />
                            <FileUploadField label="Scan Akta Kelahiran" name="dokumen_akta" accept=".pdf,.jpg,.jpeg,.png" error={errors.dokumen_akta} onChange={(file) => setData('dokumen_akta', file)} />
                            <FileUploadField label="Scan Kartu Keluarga" name="dokumen_kk" accept=".pdf,.jpg,.jpeg,.png" error={errors.dokumen_kk} onChange={(file) => setData('dokumen_kk', file)} />
                            <FileUploadField label="Scan Ijazah SD/MI" name="dokumen_ijazah" accept=".pdf,.jpg,.jpeg,.png" error={errors.dokumen_ijazah} onChange={(file) => setData('dokumen_ijazah', file)} />
                        </div>
                    </>
                ) : null}
            </ResourceForm>

            <ConfirmDeleteDialog
                open={confirmOpen}
                message={`Hapus data buku induk ${text(siswa?.nama_lengkap)}? Data siswa akan masuk arsip hapus sementara.`}
                processing={processing}
                onClose={() => setConfirmOpen(false)}
                onConfirm={destroy}
            />
        </AppLayout>
    );
}
