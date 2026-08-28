import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Download, Edit, FileText, UserRound } from 'lucide-react';
import AppLayout from '@/Layouts/AppLayout';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { formatDate } from '@/lib/format';

interface DocumentItem {
    id: number;
    jenis_dokumen: string;
    nama_file: string | null;
    url: string;
}

interface DetailProps {
    siswa: {
        id: number;
        no_urut: number | null;
        nis: string;
        nisn: string | null;
        nik: string | null;
        nama_lengkap: string;
        nama_panggilan: string | null;
        jenis_kelamin: 'L' | 'P' | null;
        tempat_lahir: string | null;
        tanggal_lahir: string | null;
        agama: string | null;
        status: string | null;
        alamat: string | null;
        rt: string | null;
        rw: string | null;
        desa_kelurahan: string | null;
        kecamatan: string | null;
        kabupaten_kota: string | null;
        provinsi: string | null;
        hp: string | null;
        foto_url: string | null;
        kelas: { nama_kelas: string } | null;
        tahun_pelajaran: { kode: string } | null;
        orang_tua_wali: Record<string, string | null> | null;
        perkembangan: Record<string, string | null>;
        dokumen: DocumentItem[];
    };
}

function value(value?: string | number | null) {
    return value || '-';
}

function genderLabel(value?: string | null) {
    if (value === 'L') {
        return 'Laki-laki';
    }

    if (value === 'P') {
        return 'Perempuan';
    }

    return '-';
}

function InfoRows({ rows }: { rows: Array<[string, string | number | null | undefined]> }) {
    return (
        <dl className="grid gap-3 text-sm">
            {rows.map(([label, content]) => (
                <div key={label} className="grid gap-1 border-b border-slate-100 pb-3 sm:grid-cols-[180px,1fr]">
                    <dt className="font-semibold text-slate-600">{label}</dt>
                    <dd className="font-medium text-slate-900">{value(content)}</dd>
                </div>
            ))}
        </dl>
    );
}

export default function BukuIndukShow({ siswa }: DetailProps) {
    const alamat = [
        siswa.alamat,
        siswa.rt || siswa.rw ? `RT ${siswa.rt ?? '0'}/RW ${siswa.rw ?? '0'}` : null,
        siswa.desa_kelurahan,
        siswa.kecamatan,
        siswa.kabupaten_kota,
        siswa.provinsi,
    ].filter(Boolean).join(', ');

    return (
        <AppLayout title="Profil Buku Induk">
            <Head title={`Buku Induk ${siswa.nama_lengkap}`} />
            <div className="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <Link href="/buku-induk">
                    <Button variant="outline" size="sm">
                        <ArrowLeft size={16} />
                        Kembali
                    </Button>
                </Link>
                <div className="flex flex-wrap gap-2">
                    <a href={`/buku-induk/${siswa.id}/export-pdf`} target="_blank" rel="noreferrer">
                        <Button variant="outline" size="sm">
                            <Download size={16} />
                            Cetak PDF
                        </Button>
                    </a>
                    <Link href={`/buku-induk/${siswa.id}/edit`}>
                        <Button size="sm">
                            <Edit size={16} />
                            Edit Data
                        </Button>
                    </Link>
                </div>
            </div>

            <div className="grid gap-4 lg:grid-cols-[320px,1fr]">
                <Card>
                    <CardContent className="grid justify-items-center gap-4 p-6 text-center">
                        {siswa.foto_url ? (
                            <img src={siswa.foto_url} alt={siswa.nama_lengkap} className="h-52 w-40 rounded-md object-cover shadow-sm" />
                        ) : (
                            <div className="grid h-52 w-40 place-items-center rounded-md bg-slate-100 text-slate-400 shadow-sm">
                                <UserRound size={56} />
                            </div>
                        )}
                        <div>
                            <h1 className="font-display text-xl font-bold text-slate-950">{siswa.nama_lengkap}</h1>
                            <p className="mt-1 text-sm font-medium text-slate-500">NIS: {siswa.nis} | NISN: {siswa.nisn ?? '-'}</p>
                            <div className="mt-3 flex justify-center">
                                <Badge variant={siswa.status === 'Aktif' ? 'success' : 'default'}>{siswa.status ?? 'Aktif'}</Badge>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <div className="grid gap-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Informasi Utama</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <InfoRows
                                rows={[
                                    ['No. Urut', siswa.no_urut],
                                    ['Nama Panggilan', siswa.nama_panggilan],
                                    ['NIK', siswa.nik],
                                    ['Tempat, Tgl Lahir', [siswa.tempat_lahir, formatDate(siswa.tanggal_lahir)].filter((item) => item && item !== '-').join(', ')],
                                    ['Jenis Kelamin', genderLabel(siswa.jenis_kelamin)],
                                    ['Agama', siswa.agama ?? 'Islam'],
                                    ['Kelas', siswa.kelas?.nama_kelas],
                                    ['Tahun Pelajaran', siswa.tahun_pelajaran?.kode],
                                    ['No. HP', siswa.hp],
                                    ['Alamat', alamat],
                                ]}
                            />
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Orang Tua / Wali</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <InfoRows
                                rows={[
                                    ['Nama Ayah', siswa.orang_tua_wali?.nama_ayah],
                                    ['Pekerjaan Ayah', siswa.orang_tua_wali?.pekerjaan_ayah],
                                    ['Nama Ibu', siswa.orang_tua_wali?.nama_ibu],
                                    ['Pekerjaan Ibu', siswa.orang_tua_wali?.pekerjaan_ibu],
                                    ['Nama Wali', siswa.orang_tua_wali?.nama_wali],
                                    ['Hubungan Wali', siswa.orang_tua_wali?.hubungan_wali],
                                ]}
                            />
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Pendidikan & Perkembangan</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <InfoRows
                                rows={[
                                    ['Asal Madrasah', siswa.perkembangan.asal_madrasah],
                                    ['Nama Madrasah Asal', siswa.perkembangan.nama_madrasah_asal],
                                    ['No. Ijazah Asal', siswa.perkembangan.no_ijazah_asal],
                                    ['Tanggal Diterima', formatDate(siswa.perkembangan.tgl_diterima)],
                                    ['Jenis Masuk', siswa.perkembangan.jenis_masuk],
                                    ['Melanjutkan Ke', siswa.perkembangan.melanjutkan_ke],
                                ]}
                            />
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Brankas Dokumen Digital</CardTitle>
                        </CardHeader>
                        <CardContent>
                            {siswa.dokumen.length ? (
                                <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                                    {siswa.dokumen.map((dokumen) => (
                                        <a
                                            key={dokumen.id}
                                            href={dokumen.url}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="flex items-center gap-3 rounded-md border border-slate-200 p-3 text-sm font-semibold text-slate-800 hover:bg-slate-50"
                                        >
                                            <FileText size={20} className="text-blue-600" />
                                            <span className="min-w-0">
                                                <span className="block truncate">{dokumen.jenis_dokumen}</span>
                                                <span className="block truncate text-xs font-medium text-slate-500">{dokumen.nama_file ?? 'Buka file'}</span>
                                            </span>
                                        </a>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-sm font-medium text-slate-500">Belum ada dokumen yang diunggah.</p>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
