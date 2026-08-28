import { Head, Link, router } from '@inertiajs/react';
import { Grid3X3, Plus } from 'lucide-react';
import { FormEvent, useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Select } from '@/Components/ui/select';

interface KelasOption {
    id: number;
    nama_kelas: string;
}

interface SesiRow {
    sesi_ke: number;
    jam_mulai: string;
    jam_selesai: string;
    hari: string[];
}

interface JadwalCell {
    jadwal_id: number;
    guru_kode: string;
    guru_nama: string;
    mapel: string;
    ruang: string | null;
}

interface JadwalIndexProps {
    kelasList: KelasOption[];
    selectedKelas: KelasOption | null;
    hariList: string[];
    sesiList: SesiRow[];
    jadwalGrid: Record<string, JadwalCell>;
    filters: {
        kelas_id?: string;
    };
}

export default function JadwalIndex({ kelasList, selectedKelas, hariList, sesiList, jadwalGrid, filters }: JadwalIndexProps) {
    const [kelasId, setKelasId] = useState(filters.kelas_id ?? '');

    const applyFilter = (event: FormEvent) => {
        event.preventDefault();
        router.get('/jadwal', { kelas_id: kelasId }, { preserveState: true, replace: true });
    };

    return (
        <AppLayout title="Jadwal Pelajaran">
            <Head title="Jadwal Pelajaran" />
            <Card>
                <CardHeader className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <CardTitle>Jadwal Pelajaran</CardTitle>
                        <p className="mt-1 text-sm font-medium text-slate-500">Lihat susunan jadwal per kelas berdasarkan hari dan sesi.</p>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        <Link href="/jadwal/grid">
                            <Button variant="outline">
                                <Grid3X3 size={17} />
                                Input Grid
                            </Button>
                        </Link>
                        <a href="/jadwal/create">
                            <Button>
                                <Plus size={17} />
                                Tambah Manual
                            </Button>
                        </a>
                    </div>
                </CardHeader>
                <CardContent>
                    <form className="mb-4 grid gap-2 sm:grid-cols-[minmax(220px,360px)_auto_auto]" onSubmit={applyFilter}>
                        <Select value={kelasId} onChange={(event) => setKelasId(event.target.value)} aria-label="Pilih kelas">
                            <option value="">Pilih Kelas</option>
                            {kelasList.map((kelas) => (
                                <option key={kelas.id} value={kelas.id}>
                                    {kelas.nama_kelas}
                                </option>
                            ))}
                        </Select>
                        <Button variant="outline" type="submit">
                            Tampilkan
                        </Button>
                        {selectedKelas ? (
                            <Button variant="ghost" type="button" onClick={() => router.get('/jadwal')}>
                                Reset
                            </Button>
                        ) : null}
                    </form>

                    {selectedKelas ? (
                        <div className="overflow-x-auto rounded-md border border-slate-200">
                            <table className="w-full min-w-[54rem] border-collapse text-sm">
                                <thead>
                                    <tr className="bg-emerald-700 text-white">
                                        <th className="w-36 px-3 py-3 text-left font-semibold">Sesi & Waktu</th>
                                        {hariList.map((hari) => (
                                            <th key={hari} className="px-3 py-3 text-center font-semibold">
                                                {hari}
                                            </th>
                                        ))}
                                    </tr>
                                </thead>
                                <tbody>
                                    {sesiList.map((sesi) => (
                                        <tr key={sesi.sesi_ke} className="border-t border-slate-200">
                                            <td className="bg-emerald-50 px-3 py-3 align-top">
                                                <div className="font-bold text-emerald-800">Sesi {sesi.sesi_ke}</div>
                                                <div className="mt-1 text-xs font-medium text-slate-600">
                                                    {sesi.jam_mulai} - {sesi.jam_selesai}
                                                </div>
                                            </td>
                                            {hariList.map((hari) => {
                                                const slot = jadwalGrid[`${hari}_${sesi.sesi_ke}`];

                                                return (
                                                    <td key={hari} className="border-l border-slate-200 px-3 py-3 text-center align-top">
                                                        {slot ? (
                                                            <div className="grid justify-items-center gap-1">
                                                                <Badge variant="success">{slot.guru_kode}</Badge>
                                                                <div className="font-semibold text-slate-800">{slot.guru_nama}</div>
                                                                <div className="text-xs font-medium text-slate-500">{slot.mapel || '-'}</div>
                                                                {slot.ruang ? <div className="text-xs text-slate-500">Ruang {slot.ruang}</div> : null}
                                                            </div>
                                                        ) : (
                                                            <span className="text-slate-400">-</span>
                                                        )}
                                                    </td>
                                                );
                                            })}
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    ) : (
                        <div className="rounded-md border border-dashed border-slate-300 px-4 py-12 text-center">
                            <div className="font-semibold text-slate-700">Pilih kelas terlebih dahulu</div>
                            <div className="mt-1 text-sm font-medium text-slate-500">Jadwal akan tampil sebagai matriks sesi dan hari.</div>
                        </div>
                    )}
                </CardContent>
            </Card>
        </AppLayout>
    );
}
