import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Loader2, Save, UserRoundCheck } from 'lucide-react';
import { FormEvent, useMemo } from 'react';
import { ErrorSummary } from '@/Components/ErrorSummary';
import AppLayout from '@/Layouts/AppLayout';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Select } from '@/Components/ui/select';
import { Textarea } from '@/Components/ui/textarea';
import { formatDate } from '@/lib/format';

interface AgendaRecord {
    id: number;
    tanggal: string;
    status: string;
    keterangan: string | null;
    guru: {
        id: number;
        kode: string;
        nama: string;
    };
}

interface JamPelajaranRecord {
    id: number;
    hari: string;
    sesi_ke: number;
    jam_mulai: string;
    jam_selesai: string;
}

interface GuruOption {
    id: number;
    kode: string;
    nama: string;
    bidang_studi: string | null;
}

interface JadwalGuruItem {
    guru_id: number;
    jam_mulai: string;
    jam_selesai: string;
}

interface PenggantiProps {
    agenda: AgendaRecord;
    jamList: JamPelajaranRecord[];
    guruPenggantiOptions: GuruOption[];
    jadwalGuru: Record<string, JadwalGuruItem[]>;
}

interface PenggantiFormData {
    jam_pelajaran_id: string;
    guru_pengganti_id: string;
    keterangan: string;
}

function hasConflict(jadwal: JadwalGuruItem[], jam?: JamPelajaranRecord) {
    if (!jam) {
        return false;
    }

    return jadwal.some((item) => item.jam_mulai <= jam.jam_selesai && item.jam_selesai >= jam.jam_mulai);
}

export default function AbsensiPengganti({ agenda, jamList, guruPenggantiOptions, jadwalGuru }: PenggantiProps) {
    const { data, setData, post, processing, errors } = useForm<PenggantiFormData>({
        jam_pelajaran_id: '',
        guru_pengganti_id: '',
        keterangan: '',
    });

    const selectedJam = jamList.find((jam) => jam.id.toString() === data.jam_pelajaran_id);
    const availableGurus = useMemo(
        () => guruPenggantiOptions.filter((guru) => !hasConflict(jadwalGuru[guru.id] ?? [], selectedJam)),
        [guruPenggantiOptions, jadwalGuru, selectedJam],
    );

    const submit = (event: FormEvent) => {
        event.preventDefault();
        post(`/absensi/pengganti/${agenda.id}`);
    };

    return (
        <AppLayout title="Tunjuk Guru Pengganti">
            <Head title="Tunjuk Guru Pengganti" />
            <div className="mb-4">
                <Link href={`/absensi?tanggal=${agenda.tanggal}`}>
                    <Button variant="outline">
                        <ArrowLeft size={17} />
                        Kembali ke Absensi
                    </Button>
                </Link>
            </div>

            <div className="grid gap-4 xl:grid-cols-[minmax(0,1fr)_22rem]">
                <Card>
                    <CardHeader>
                        <CardTitle>Tunjuk Guru Pengganti</CardTitle>
                        <div className="mt-2 flex flex-wrap items-center gap-2 text-sm font-medium text-slate-500">
                            <Badge>{agenda.guru.kode}</Badge>
                            <span className="font-semibold text-slate-900">{agenda.guru.nama}</span>
                            <span>{formatDate(agenda.tanggal)}</span>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <form className="grid gap-4" onSubmit={submit}>
                            <ErrorSummary errors={errors} />
                            <div className="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label className="mb-2 block text-sm font-semibold text-slate-700" htmlFor="jam_pelajaran_id">
                                        Sesi Jam Pelajaran
                                    </label>
                                    <Select
                                        id="jam_pelajaran_id"
                                        value={data.jam_pelajaran_id}
                                        required
                                        onChange={(event) => {
                                            setData({
                                                ...data,
                                                jam_pelajaran_id: event.target.value,
                                                guru_pengganti_id: '',
                                            });
                                        }}
                                    >
                                        <option value="">Pilih Sesi</option>
                                        {jamList.map((jam) => (
                                            <option key={jam.id} value={jam.id}>
                                                {jam.hari} - Sesi {jam.sesi_ke} ({jam.jam_mulai} - {jam.jam_selesai})
                                            </option>
                                        ))}
                                    </Select>
                                    <p className="mt-1 text-xs font-medium text-slate-500">Pilih jam yang akan digantikan.</p>
                                </div>
                                <div>
                                    <label className="mb-2 block text-sm font-semibold text-slate-700" htmlFor="guru_pengganti_id">
                                        Guru Pengganti
                                    </label>
                                    <Select
                                        id="guru_pengganti_id"
                                        value={data.guru_pengganti_id}
                                        required
                                        disabled={!data.jam_pelajaran_id}
                                        onChange={(event) => setData('guru_pengganti_id', event.target.value)}
                                    >
                                        <option value="">{data.jam_pelajaran_id ? 'Pilih Guru' : 'Pilih sesi dulu'}</option>
                                        {availableGurus.map((guru) => (
                                            <option key={guru.id} value={guru.id}>
                                                {guru.kode} - {guru.nama}{guru.bidang_studi ? ` (${guru.bidang_studi})` : ''}
                                            </option>
                                        ))}
                                    </Select>
                                    <p className="mt-1 text-xs font-medium text-slate-500">Guru yang bentrok jadwal otomatis disembunyikan.</p>
                                </div>
                            </div>

                            <div>
                                <label className="mb-2 block text-sm font-semibold text-slate-700" htmlFor="keterangan">
                                    Keterangan
                                </label>
                                <Textarea
                                    id="keterangan"
                                    value={data.keterangan}
                                    placeholder="Catatan tentang penggantian"
                                    onChange={(event) => setData('keterangan', event.target.value)}
                                />
                            </div>

                            <div className="flex justify-end">
                                <Button type="submit" disabled={processing || !data.jam_pelajaran_id || !data.guru_pengganti_id}>
                                    {processing ? <Loader2 size={17} className="animate-spin" /> : <Save size={17} />}
                                    Tugaskan Pengganti
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent>
                        <div className="mb-3 flex h-12 w-12 items-center justify-center rounded-md bg-blue-50 text-blue-700">
                            <UserRoundCheck size={24} />
                        </div>
                        <div className="font-semibold text-slate-900">Pemeriksaan Konflik Jadwal</div>
                        <p className="mt-2 text-sm font-medium text-slate-500">
                            Sistem memeriksa jadwal tetap guru pada hari dan sesi yang sama sebelum penugasan disimpan.
                        </p>
                        {data.jam_pelajaran_id ? (
                            <div className="mt-4 rounded-md bg-slate-50 p-3 text-sm font-semibold text-slate-700">
                                {availableGurus.length} guru tersedia untuk sesi ini.
                            </div>
                        ) : null}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
