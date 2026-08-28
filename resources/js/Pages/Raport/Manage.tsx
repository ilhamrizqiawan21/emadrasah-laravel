import { Head, Link, router, useForm } from '@inertiajs/react';
import { ArrowLeft, Download, Loader2, Save } from 'lucide-react';
import { FormEvent, useMemo, useState } from 'react';
import { ErrorSummary } from '@/Components/ErrorSummary';
import AppLayout from '@/Layouts/AppLayout';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Select } from '@/Components/ui/select';
import { Textarea } from '@/Components/ui/textarea';

interface KelasOption {
    id: number;
    nama_kelas: string;
}

interface SiswaRecord {
    id: number;
    nis: string;
    nisn: string | null;
    nama_lengkap: string;
    status: string | null;
    kelas: KelasOption | null;
}

interface TahunPelajaranOption {
    id: number;
    kode: string;
    is_aktif: boolean;
}

interface MapelRecord {
    id: number;
    nama_mapel: string;
    jp_per_sesi: number | null;
    parent_id: number | null;
    urut: number | null;
}

interface NilaiRecord {
    id: number;
    mapel_id: number;
    nilai_akhir: number | null;
    deskripsi: string | null;
}

interface NilaiFormRow {
    angka: string;
    capaian: string;
}

interface RaportFormData {
    tahun_pelajaran_id: string;
    semester: string;
    nilai: Record<string, NilaiFormRow>;
}

interface RaportManageProps {
    siswa: SiswaRecord;
    tahunPelajaran: TahunPelajaranOption[];
    selectedTahunPelajaranId: number | null;
    semester: number;
    mapels: MapelRecord[];
    nilai: NilaiRecord[];
}

function initialNilai(mapels: MapelRecord[], nilai: NilaiRecord[]) {
    const nilaiByMapel = new Map(nilai.map((item) => [item.mapel_id, item]));

    return mapels.reduce<Record<string, NilaiFormRow>>((carry, mapel) => {
        const current = nilaiByMapel.get(mapel.id);
        carry[mapel.id] = {
            angka: current?.nilai_akhir?.toString() ?? '',
            capaian: current?.deskripsi ?? '',
        };

        return carry;
    }, {});
}

export default function RaportManage({
    siswa,
    tahunPelajaran,
    selectedTahunPelajaranId,
    semester,
    mapels,
    nilai,
}: RaportManageProps) {
    const [selectedTp, setSelectedTp] = useState(selectedTahunPelajaranId?.toString() ?? '');
    const [selectedSemester, setSelectedSemester] = useState(semester.toString());
    const [filtering, setFiltering] = useState(false);
    const initialRows = useMemo(() => initialNilai(mapels, nilai), [mapels, nilai]);
    const { data, setData, post, processing, errors } = useForm<RaportFormData>({
        tahun_pelajaran_id: selectedTahunPelajaranId?.toString() ?? '',
        semester: semester.toString(),
        nilai: initialRows,
    });

    const applySelection = (event: FormEvent) => {
        event.preventDefault();
        setFiltering(true);
        router.get(`/raport/${siswa.id}/manage`, {
            tahun_pelajaran_id: selectedTp,
            semester: selectedSemester,
        }, {
            preserveState: false,
            replace: true,
            onFinish: () => setFiltering(false),
        });
    };

    const updateNilai = (mapelId: number, field: keyof NilaiFormRow, value: string) => {
        setData('nilai', {
            ...data.nilai,
            [mapelId]: {
                ...(data.nilai[mapelId] ?? { angka: '', capaian: '' }),
                [field]: value,
            },
        });
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();
        post(`/raport/${siswa.id}/store`, {
            preserveScroll: true,
        });
    };

    const pdfHref = `/raport/${siswa.id}/export-pdf?tahun_pelajaran_id=${encodeURIComponent(data.tahun_pelajaran_id)}&semester=${encodeURIComponent(data.semester)}`;
    const canSubmit = Boolean(data.tahun_pelajaran_id && mapels.length);

    return (
        <AppLayout title="Kelola Nilai Raport">
            <Head title="Kelola Nilai Raport" />
            <div className="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div className="flex min-w-0 items-center gap-3">
                    <div className="grid h-12 w-12 shrink-0 place-items-center rounded-md bg-blue-600 text-lg font-bold text-white">
                        {siswa.nama_lengkap.charAt(0).toUpperCase()}
                    </div>
                    <div className="min-w-0">
                        <h1 className="truncate font-display text-xl font-bold text-slate-950">{siswa.nama_lengkap}</h1>
                        <div className="mt-1 flex flex-wrap items-center gap-2 text-sm font-medium text-slate-500">
                            <span>NIS: {siswa.nis}</span>
                            <span>NISN: {siswa.nisn ?? '-'}</span>
                            <Badge>{siswa.kelas?.nama_kelas ?? '-'}</Badge>
                        </div>
                    </div>
                </div>
                <div className="flex flex-wrap gap-2">
                    {data.tahun_pelajaran_id ? (
                        <a href={pdfHref} target="_blank" rel="noreferrer">
                            <Button variant="danger" type="button">
                                <Download size={17} />
                                Cetak PDF
                            </Button>
                        </a>
                    ) : (
                        <Button variant="danger" type="button" disabled>
                            <Download size={17} />
                            Cetak PDF
                        </Button>
                    )}
                    <Link href="/raport">
                        <Button variant="outline">
                            <ArrowLeft size={17} />
                            Kembali
                        </Button>
                    </Link>
                </div>
            </div>

            <Card className="mb-4">
                <CardContent>
                    <form className="grid gap-3 md:grid-cols-[minmax(220px,1fr)_180px_auto]" onSubmit={applySelection}>
                        <Select value={selectedTp} onChange={(event) => setSelectedTp(event.target.value)} aria-label="Pilih tahun pelajaran">
                            <option value="">Pilih Tahun Pelajaran</option>
                            {tahunPelajaran.map((item) => (
                                <option key={item.id} value={item.id}>
                                    {item.kode}{item.is_aktif ? ' (Aktif)' : ''}
                                </option>
                            ))}
                        </Select>
                        <Select value={selectedSemester} onChange={(event) => setSelectedSemester(event.target.value)} aria-label="Pilih semester">
                            <option value="1">Semester 1</option>
                            <option value="2">Semester 2</option>
                        </Select>
                        <Button variant="outline" type="submit" disabled={filtering}>
                            {filtering ? <Loader2 size={16} className="animate-spin" /> : null}
                            Terapkan
                        </Button>
                    </form>
                </CardContent>
            </Card>

            <form onSubmit={submit}>
                <Card>
                    <CardHeader className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <CardTitle>Daftar Nilai Mata Pelajaran</CardTitle>
                            <p className="mt-1 text-sm font-medium text-slate-500">Data tersimpan sebagai arsip nilai Buku Induk.</p>
                        </div>
                        <Button type="submit" disabled={processing || !canSubmit}>
                            {processing ? <Loader2 size={17} className="animate-spin" /> : <Save size={17} />}
                            Simpan Nilai
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <ErrorSummary errors={errors} />
                        {mapels.length ? (
                            <div className="mt-4 overflow-hidden rounded-md border border-slate-200">
                                <div className="hidden grid-cols-[minmax(180px,1fr)_130px_minmax(260px,1.5fr)] gap-3 border-b border-slate-200 bg-slate-50 px-4 py-3 text-xs font-bold uppercase text-slate-500 md:grid">
                                    <span>Mata Pelajaran</span>
                                    <span>Nilai Akhir</span>
                                    <span>Capaian Kompetensi</span>
                                </div>
                                <div className="divide-y divide-slate-200">
                                    {mapels.map((mapel) => (
                                        <div key={mapel.id} className="grid gap-3 px-4 py-4 md:grid-cols-[minmax(180px,1fr)_130px_minmax(260px,1.5fr)] md:items-start">
                                            <div>
                                                <div className="font-semibold text-slate-900">{mapel.nama_mapel}</div>
                                                <div className="mt-1 text-xs font-medium text-slate-500">
                                                    JP per sesi: {mapel.jp_per_sesi ?? '-'}
                                                </div>
                                            </div>
                                            <Input
                                                type="number"
                                                min={0}
                                                max={100}
                                                inputMode="numeric"
                                                value={data.nilai[mapel.id]?.angka ?? ''}
                                                placeholder="0"
                                                aria-label={`Nilai akhir ${mapel.nama_mapel}`}
                                                onChange={(event) => updateNilai(mapel.id, 'angka', event.target.value)}
                                            />
                                            <Textarea
                                                className="min-h-20"
                                                value={data.nilai[mapel.id]?.capaian ?? ''}
                                                placeholder="Deskripsi capaian kompetensi"
                                                aria-label={`Capaian kompetensi ${mapel.nama_mapel}`}
                                                onChange={(event) => updateNilai(mapel.id, 'capaian', event.target.value)}
                                            />
                                        </div>
                                    ))}
                                </div>
                            </div>
                        ) : (
                            <div className="mt-4 rounded-md border border-dashed border-slate-300 px-4 py-10 text-center">
                                <div className="font-semibold text-slate-700">Mata pelajaran belum tersedia</div>
                                <div className="mt-1 text-sm font-medium text-slate-500">Tambahkan master mata pelajaran sebelum mengisi rapor.</div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </form>
        </AppLayout>
    );
}
