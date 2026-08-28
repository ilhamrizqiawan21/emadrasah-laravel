import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, IdCard, Loader2, Save, Search } from 'lucide-react';
import { ChangeEvent, KeyboardEvent, useMemo, useState } from 'react';
import { Fragment } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Dialog } from '@/Components/ui/dialog';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';

interface KelasOption {
    id: number;
    nama_kelas: string;
}

interface JamPelajaranRow {
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

interface MapelOption {
    id: number;
    nama_mapel: string;
}

interface JadwalCell {
    jadwal_id: number;
    guru_id: number;
    guru_kode: string;
    guru_nama: string;
    mapel_id: number;
    mapel: string;
}

interface CellState {
    jadwal_id: number | null;
    guru_id: number | null;
    mapel_id: number | null;
    original: string;
    value: string;
    guru_nama: string;
    mapel: string;
    state: 'empty' | 'filled' | 'changed' | 'invalid' | 'saving';
}

interface JadwalGridProps {
    kelas: KelasOption[];
    jamPelajaran: JamPelajaranRow[];
    gurus: GuruOption[];
    mapels: MapelOption[];
    jadwalGrid: Record<string, JadwalCell>;
}

function csrfToken() {
    return document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
}

function buildInitialCells(kelas: KelasOption[], jamPelajaran: JamPelajaranRow[], jadwalGrid: Record<string, JadwalCell>) {
    return jamPelajaran.reduce<Record<string, CellState>>((carry, jam) => {
        kelas.forEach((kelasItem) => {
            const key = `${kelasItem.id}_${jam.hari}_${jam.id}`;
            const existing = jadwalGrid[key];
            carry[key] = {
                jadwal_id: existing?.jadwal_id ?? null,
                guru_id: existing?.guru_id ?? null,
                mapel_id: existing?.mapel_id ?? null,
                original: existing?.guru_kode ?? '',
                value: existing?.guru_kode ?? '',
                guru_nama: existing?.guru_nama ?? '',
                mapel: existing?.mapel ?? '',
                state: existing?.guru_kode ? 'filled' : 'empty',
            };
        });

        return carry;
    }, {});
}

export default function JadwalGrid({ kelas, jamPelajaran, gurus, mapels, jadwalGrid }: JadwalGridProps) {
    const guruByKode = useMemo(() => new Map(gurus.map((guru) => [guru.kode.toUpperCase(), guru])), [gurus]);
    const [cells, setCells] = useState(() => buildInitialCells(kelas, jamPelajaran, jadwalGrid));
    const [pendingKeys, setPendingKeys] = useState<Set<string>>(new Set());
    const [currentKey, setCurrentKey] = useState<string | null>(null);
    const [dialogOpen, setDialogOpen] = useState(false);
    const [guruSearch, setGuruSearch] = useState('');
    const [saving, setSaving] = useState(false);
    const [status, setStatus] = useState('Siap mengisi jadwal');

    const filteredGurus = gurus.filter((guru) => {
        const query = guruSearch.toLowerCase();

        return [guru.kode, guru.nama, guru.bidang_studi ?? ''].join(' ').toLowerCase().includes(query);
    });

    const groupedJam = jamPelajaran.reduce<Array<{ hari: string; rows: JamPelajaranRow[] }>>((carry, jam) => {
        const last = carry[carry.length - 1];
        if (last?.hari === jam.hari) {
            last.rows.push(jam);
        } else {
            carry.push({ hari: jam.hari, rows: [jam] });
        }

        return carry;
    }, []);

    const inferMapelId = (guru: GuruOption, current: CellState) => {
        if (current.mapel_id) {
            return current.mapel_id;
        }

        const bidang = guru.bidang_studi?.toLowerCase() ?? '';
        const found = mapels.find((mapel) => bidang.includes(mapel.nama_mapel.toLowerCase().split('/')[0].trim()));

        return found?.id ?? mapels[0]?.id ?? null;
    };

    const queueCell = (key: string, nextValue: string) => {
        const normalized = nextValue.trim().toUpperCase();
        setCells((current) => {
            const cell = current[key];
            if (!cell) {
                return current;
            }

            let nextState: CellState['state'] = normalized ? 'changed' : 'empty';
            let guruId = cell.guru_id;
            let guruNama = cell.guru_nama;

            if (normalized && !guruByKode.has(normalized)) {
                nextState = 'invalid';
                guruId = null;
                guruNama = '';
            } else if (normalized) {
                const guru = guruByKode.get(normalized);
                guruId = guru?.id ?? null;
                guruNama = guru?.nama ?? '';
            }

            if (normalized === cell.original) {
                nextState = normalized ? 'filled' : 'empty';
            }

            return {
                ...current,
                [key]: {
                    ...cell,
                    value: normalized,
                    guru_id: guruId,
                    guru_nama: guruNama,
                    state: nextState,
                },
            };
        });

        setPendingKeys((current) => {
            const next = new Set(current);
            const original = cells[key]?.original ?? '';
            if (normalized !== original && (normalized === '' || guruByKode.has(normalized))) {
                next.add(key);
            } else {
                next.delete(key);
            }
            return next;
        });
    };

    const updateValue = (key: string, event: ChangeEvent<HTMLInputElement>) => {
        const value = event.target.value.toUpperCase();
        setCells((current) => {
            const cell = current[key];
            if (!cell) {
                return current;
            }

            return {
                ...current,
                [key]: {
                    ...cell,
                    value,
                    state: value && !guruByKode.has(value) ? 'invalid' : value === cell.original ? (value ? 'filled' : 'empty') : 'changed',
                },
            };
        });
    };

    const insertKode = (kode: string) => {
        if (!currentKey) {
            setStatus('Pilih sel terlebih dahulu.');
            return;
        }

        queueCell(currentKey, kode);
        setDialogOpen(false);
        window.setTimeout(() => document.querySelector<HTMLInputElement>(`[data-cell-key="${currentKey}"]`)?.focus(), 0);
    };

    const navigate = (index: number, direction: 'left' | 'right' | 'up' | 'down') => {
        const cols = kelas.length;
        const nextIndex = {
            left: index - 1,
            right: index + 1,
            up: index - cols,
            down: index + cols,
        }[direction];

        document.querySelector<HTMLInputElement>(`[data-grid-index="${nextIndex}"]`)?.focus();
    };

    const handleKey = (key: string, index: number, event: KeyboardEvent<HTMLInputElement>) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            queueCell(key, cells[key]?.value ?? '');
            navigate(index, 'right');
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            queueCell(key, cells[key]?.original ?? '');
        }

        if (event.key === 'ArrowRight') {
            event.preventDefault();
            navigate(index, 'right');
        }

        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            navigate(index, 'left');
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            navigate(index, 'down');
        }

        if (event.key === 'ArrowUp') {
            event.preventDefault();
            navigate(index, 'up');
        }
    };

    const saveAll = async () => {
        const keys = Array.from(pendingKeys);
        if (!keys.length) {
            return;
        }

        setSaving(true);
        setStatus(`Menyimpan ${keys.length} perubahan...`);

        let errors = 0;
        for (const key of keys) {
            const cell = cells[key];
            const input = document.querySelector<HTMLInputElement>(`[data-cell-key="${key}"]`);
            const kelasId = input?.dataset.kelasId;
            const hari = input?.dataset.hari;
            const jamId = input?.dataset.jamId;
            const guru = guruByKode.get(cell.value);

            setCells((current) => ({ ...current, [key]: { ...current[key], state: 'saving' } }));

            try {
                if (cell.value === '' && cell.jadwal_id) {
                    const response = await fetch(`/jadwal/${cell.jadwal_id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken(),
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Gagal menghapus slot jadwal.');
                    }

                    setCells((current) => ({
                        ...current,
                        [key]: {
                            ...current[key],
                            jadwal_id: null,
                            guru_id: null,
                            mapel_id: null,
                            original: '',
                            value: '',
                            guru_nama: '',
                            mapel: '',
                            state: 'empty',
                        },
                    }));
                    continue;
                }

                if (!guru || !kelasId || !hari || !jamId) {
                    throw new Error('Data slot tidak lengkap.');
                }

                const mapelId = inferMapelId(guru, cell);
                if (!mapelId) {
                    throw new Error('Mata pelajaran belum tersedia.');
                }

                const response = await fetch('/jadwal/grid-store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        kelas_id: kelasId,
                        hari,
                        jam_id: jamId,
                        guru_id: guru.id,
                        mapel_id: mapelId,
                    }),
                });

                const result = await response.json();
                if (!response.ok || result.status !== 'success') {
                    throw new Error(result.message ?? 'Gagal menyimpan slot jadwal.');
                }

                const mapel = mapels.find((item) => item.id === mapelId);
                setCells((current) => ({
                    ...current,
                    [key]: {
                        ...current[key],
                        jadwal_id: result.jadwal.id,
                        guru_id: guru.id,
                        mapel_id: mapelId,
                        original: guru.kode,
                        value: guru.kode,
                        guru_nama: guru.nama,
                        mapel: mapel?.nama_mapel ?? current[key].mapel,
                        state: 'filled',
                    },
                }));
            } catch (error) {
                errors += 1;
                setCells((current) => ({ ...current, [key]: { ...current[key], state: 'invalid' } }));
            }
        }

        setPendingKeys(new Set());
        setSaving(false);
        setStatus(errors ? `Selesai dengan ${errors} konflik. Periksa sel merah.` : 'Semua perubahan berhasil disimpan.');
    };

    const cellClassName = (state: CellState['state']) => {
        if (state === 'invalid') {
            return 'border-rose-300 bg-rose-50 text-rose-800 focus:border-rose-500 focus:ring-rose-100';
        }

        if (state === 'changed') {
            return 'border-amber-300 bg-amber-50 text-amber-900 focus:border-amber-500 focus:ring-amber-100';
        }

        if (state === 'filled') {
            return 'border-emerald-200 bg-emerald-50 text-emerald-900';
        }

        return 'bg-white';
    };

    return (
        <AppLayout title="Input Jadwal Grid">
            <Head title="Input Jadwal Grid" />
            <Card>
                <CardHeader className="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <CardTitle>Input Jadwal</CardTitle>
                        <p className="mt-1 text-sm font-medium text-slate-500">Ketik kode guru pada sel, lalu simpan perubahan yang sudah valid.</p>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        <Link href="/jadwal">
                            <Button variant="outline">
                                <ArrowLeft size={17} />
                                Kembali
                            </Button>
                        </Link>
                        <Button variant="outline" type="button" onClick={() => setDialogOpen(true)}>
                            <IdCard size={17} />
                            Daftar Kode
                        </Button>
                        <Button type="button" disabled={saving || pendingKeys.size === 0} onClick={saveAll}>
                            {saving ? <Loader2 size={17} className="animate-spin" /> : <Save size={17} />}
                            Simpan Semua
                            {pendingKeys.size ? <Badge className="bg-white/20 text-white">{pendingKeys.size}</Badge> : null}
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div className="mb-4 flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-600">
                        <span className="inline-flex items-center gap-1"><span className="h-2 w-2 rounded-full bg-emerald-500" />Terisi</span>
                        <span className="inline-flex items-center gap-1"><span className="h-2 w-2 rounded-full bg-amber-500" />Berubah</span>
                        <span className="inline-flex items-center gap-1"><span className="h-2 w-2 rounded-full bg-rose-500" />Konflik</span>
                        <span className="ml-auto text-sm font-semibold text-slate-700">{status}</span>
                    </div>

                    <div className="overflow-x-auto rounded-md border border-slate-200">
                        <table className="w-full min-w-[72rem] border-collapse text-sm">
                            <thead>
                                <tr className="bg-slate-900 text-white">
                                    <th className="sticky left-0 z-10 w-36 bg-slate-900 px-3 py-3 text-left font-semibold">Sesi & Waktu</th>
                                    {kelas.map((item) => (
                                        <th key={item.id} className="px-3 py-3 text-center font-semibold">
                                            {item.nama_kelas}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody>
                                {groupedJam.map((group) => (
                                    <Fragment key={group.hari}>
                                        <tr key={`${group.hari}-label`}>
                                            <td colSpan={kelas.length + 1} className="bg-blue-50 px-4 py-2 font-bold text-blue-800">
                                                {group.hari}
                                            </td>
                                        </tr>
                                        {group.rows.map((jam) => (
                                            <tr key={jam.id} className="border-t border-slate-200">
                                                <td className="sticky left-0 z-10 bg-slate-50 px-3 py-3 align-top">
                                                    <div className="font-bold text-slate-900">Sesi {jam.sesi_ke}</div>
                                                    <div className="mt-1 text-xs font-medium text-slate-500">
                                                        {jam.jam_mulai} - {jam.jam_selesai}
                                                    </div>
                                                </td>
                                                {kelas.map((kelasItem, kelasIndex) => {
                                                    const key = `${kelasItem.id}_${jam.hari}_${jam.id}`;
                                                    const cell = cells[key];
                                                    const index = jamPelajaran.findIndex((item) => item.id === jam.id) * kelas.length + kelasIndex;

                                                    return (
                                                        <td key={key} className="border-l border-slate-200 px-2 py-2 align-top">
                                                            <Input
                                                                className={`h-9 text-center font-bold uppercase ${cellClassName(cell.state)}`}
                                                                value={cell.value}
                                                                placeholder="-"
                                                                data-cell-key={key}
                                                                data-grid-index={index}
                                                                data-kelas-id={kelasItem.id}
                                                                data-hari={jam.hari}
                                                                data-jam-id={jam.id}
                                                                aria-label={`Kode guru ${kelasItem.nama_kelas} ${jam.hari} sesi ${jam.sesi_ke}`}
                                                                onFocus={(event) => {
                                                                    setCurrentKey(key);
                                                                    event.target.select();
                                                                }}
                                                                onBlur={() => queueCell(key, cell.value)}
                                                                onChange={(event) => updateValue(key, event)}
                                                                onKeyDown={(event) => handleKey(key, index, event)}
                                                            />
                                                            {cell.guru_nama ? (
                                                                <div className="mt-1 truncate text-center text-[11px] font-medium text-slate-500" title={cell.guru_nama}>
                                                                    {cell.guru_nama}
                                                                </div>
                                                            ) : null}
                                                        </td>
                                                    );
                                                })}
                                            </tr>
                                        ))}
                                    </Fragment>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <Dialog open={dialogOpen} title="Daftar Kode Guru" onClose={() => setDialogOpen(false)}>
                <div className="mb-3 flex items-center gap-2">
                    <Search size={18} className="shrink-0 text-slate-400" />
                    <Input value={guruSearch} placeholder="Cari kode, nama, atau bidang studi" onChange={(event) => setGuruSearch(event.target.value)} />
                </div>
                <div className="max-h-96 overflow-y-auto rounded-md border border-slate-200">
                    <table className="w-full text-sm">
                        <thead className="sticky top-0 bg-slate-50 text-xs uppercase text-slate-500">
                            <tr>
                                <th className="px-3 py-2 text-left">Kode</th>
                                <th className="px-3 py-2 text-left">Nama Guru</th>
                                <th className="px-3 py-2 text-left">Bidang</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-200">
                            {filteredGurus.map((guru) => (
                                <tr key={guru.id} className="cursor-pointer hover:bg-blue-50" onClick={() => insertKode(guru.kode)}>
                                    <td className="px-3 py-2">
                                        <Badge variant="info">{guru.kode}</Badge>
                                    </td>
                                    <td className="px-3 py-2 font-semibold text-slate-800">{guru.nama}</td>
                                    <td className="px-3 py-2 text-slate-500">{guru.bidang_studi ?? '-'}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </Dialog>
        </AppLayout>
    );
}
