import { Head, Link, router, useForm } from '@inertiajs/react';
import { CalendarCheck, CalendarDays, ChartLine, Loader2, Save, UserCheck, UserX } from 'lucide-react';
import { FormEvent, useMemo, useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Select } from '@/Components/ui/select';
import { Table, TableContainer, TBody, Td, Th, THead, Tr } from '@/Components/ui/table';
import { formatDate, formatNumber } from '@/lib/format';

type Status = 'hadir' | 'izin' | 'sakit' | 'alpha';

interface GuruRecord {
    id: number;
    kode: string;
    nama: string;
    bidang_studi: string | null;
}

interface AgendaRecord {
    id: number;
    status: Status;
    keterangan: string | null;
    pengganti: {
        id: number;
        guru_nama: string | null;
    } | null;
}

interface AbsensiRow {
    guru: GuruRecord;
    agenda: AgendaRecord | null;
}

interface AbsensiIndexProps {
    tanggal: string;
    rows: AbsensiRow[];
    summary: {
        total_guru: number;
        hadir: number;
        izin: number;
        sakit: number;
        alpha: number;
    };
}

interface AbsensiFormData {
    tanggal: string;
    status: Record<string, Status>;
    keterangan: Record<string, string>;
}

const statusLabels: Record<Status, string> = {
    hadir: 'Hadir',
    izin: 'Izin',
    sakit: 'Sakit',
    alpha: 'Alpha',
};

const statusVariants: Record<Status, 'success' | 'warning' | 'info' | 'danger'> = {
    hadir: 'success',
    izin: 'warning',
    sakit: 'info',
    alpha: 'danger',
};

export default function AbsensiIndex({ tanggal, rows, summary }: AbsensiIndexProps) {
    const [selectedDate, setSelectedDate] = useState(tanggal);
    const initialStatus = useMemo(
        () => Object.fromEntries(rows.map((row) => [row.guru.id, row.agenda?.status ?? 'hadir'])) as Record<string, Status>,
        [rows],
    );
    const initialKeterangan = useMemo(
        () => Object.fromEntries(rows.map((row) => [row.guru.id, row.agenda?.keterangan ?? ''])) as Record<string, string>,
        [rows],
    );
    const { data, setData, post, processing } = useForm<AbsensiFormData>({
        tanggal,
        status: initialStatus,
        keterangan: initialKeterangan,
    });

    const hadirPersen = summary.total_guru > 0 ? Math.round((summary.hadir / summary.total_guru) * 100) : 0;
    const tidakHadir = summary.izin + summary.sakit + summary.alpha;

    const applyDate = (event: FormEvent) => {
        event.preventDefault();
        router.get('/absensi', { tanggal: selectedDate }, { preserveState: false, replace: true });
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();
        post('/absensi');
    };

    const setStatus = (guruId: number, status: Status) => {
        setData('status', { ...data.status, [guruId]: status });
    };

    const setKeterangan = (guruId: number, value: string) => {
        setData('keterangan', { ...data.keterangan, [guruId]: value });
    };

    const statCards = [
        { label: 'Total Guru', value: summary.total_guru, helper: 'guru', icon: CalendarDays, tone: 'bg-blue-50 text-blue-700' },
        { label: 'Hadir', value: summary.hadir, helper: `${hadirPersen}%`, icon: UserCheck, tone: 'bg-emerald-50 text-emerald-700' },
        { label: 'Tidak Hadir', value: tidakHadir, helper: 'izin, sakit, alpha', icon: UserX, tone: 'bg-amber-50 text-amber-700' },
        { label: 'Alpha', value: summary.alpha, helper: 'tanpa keterangan', icon: CalendarCheck, tone: 'bg-rose-50 text-rose-700' },
    ];

    return (
        <AppLayout title="Absensi Guru">
            <Head title="Absensi Guru" />
            <div className="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                {statCards.map((stat) => {
                    const Icon = stat.icon;

                    return (
                        <Card key={stat.label}>
                            <CardContent className="flex items-center justify-between">
                                <div>
                                    <div className="text-sm font-semibold text-slate-500">{stat.label}</div>
                                    <div className="mt-2 font-display text-3xl font-extrabold text-slate-950">{formatNumber(stat.value)}</div>
                                    <div className="mt-1 text-xs font-semibold text-slate-500">{stat.helper}</div>
                                </div>
                                <div className={`flex h-12 w-12 items-center justify-center rounded-md ${stat.tone}`}>
                                    <Icon size={22} />
                                </div>
                            </CardContent>
                        </Card>
                    );
                })}
            </div>

            <Card>
                <CardHeader className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <CardTitle>Absensi Guru Harian</CardTitle>
                        <p className="mt-1 text-sm font-medium text-slate-500">Catat kehadiran guru untuk tanggal yang dipilih.</p>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        <a href="/absensi/rekap">
                            <Button variant="outline">
                                <ChartLine size={17} />
                                Rekap Bulanan
                            </Button>
                        </a>
                        <Button variant="secondary" type="button" onClick={() => router.get('/absensi')}>
                            Hari Ini
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <form className="mb-4 grid gap-2 sm:grid-cols-[220px_auto]" onSubmit={applyDate}>
                        <Input type="date" value={selectedDate} onChange={(event) => setSelectedDate(event.target.value)} aria-label="Pilih tanggal absensi" />
                        <Button variant="outline" type="submit">
                            Tampilkan
                        </Button>
                    </form>

                    <form onSubmit={submit}>
                        <input type="hidden" name="tanggal" value={data.tanggal} />
                        <div className="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <div className="text-sm font-semibold text-slate-600">
                                Absensi tanggal <span className="text-blue-700">{formatDate(data.tanggal)}</span>
                            </div>
                            <Badge variant="info">Status tidak hadir membutuhkan guru pengganti</Badge>
                        </div>

                        <TableContainer>
                            <Table className="min-w-[58rem]">
                                <THead>
                                    <Tr>
                                        <Th className="w-16 text-center">No</Th>
                                        <Th>Kode</Th>
                                        <Th>Nama Guru</Th>
                                        <Th className="w-40">Status</Th>
                                        <Th>Keterangan</Th>
                                        <Th className="w-56 text-center">Guru Pengganti</Th>
                                    </Tr>
                                </THead>
                                <TBody>
                                    {rows.map((row, index) => {
                                        const currentStatus = data.status[row.guru.id] ?? row.agenda?.status ?? 'hadir';

                                        return (
                                            <Tr key={row.guru.id}>
                                                <Td className="text-center">{index + 1}</Td>
                                                <Td><Badge>{row.guru.kode}</Badge></Td>
                                                <Td>
                                                    <div className="font-semibold text-slate-900">{row.guru.nama}</div>
                                                    <div className="mt-1 text-xs font-medium text-slate-500">{row.guru.bidang_studi ?? '-'}</div>
                                                </Td>
                                                <Td>
                                                    <Select
                                                        value={currentStatus}
                                                        onChange={(event) => setStatus(row.guru.id, event.target.value as Status)}
                                                        aria-label={`Status ${row.guru.nama}`}
                                                    >
                                                        {Object.entries(statusLabels).map(([value, label]) => (
                                                            <option key={value} value={value}>
                                                                {label}
                                                            </option>
                                                        ))}
                                                    </Select>
                                                </Td>
                                                <Td>
                                                    <Input
                                                        value={data.keterangan[row.guru.id] ?? ''}
                                                        placeholder="Opsional"
                                                        onChange={(event) => setKeterangan(row.guru.id, event.target.value)}
                                                        aria-label={`Keterangan ${row.guru.nama}`}
                                                    />
                                                </Td>
                                                <Td className="text-center">
                                                    {currentStatus !== 'hadir' ? (
                                                        row.agenda?.id ? (
                                                            <div className="grid justify-items-center gap-2">
                                                                <Link href={`/absensi/pengganti/${row.agenda.id}`}>
                                                                    <Button variant="outline" size="sm" type="button">
                                                                        Tunjuk Pengganti
                                                                    </Button>
                                                                </Link>
                                                                {row.agenda.pengganti ? (
                                                                    <Badge variant="success">{row.agenda.pengganti.guru_nama ?? '-'}</Badge>
                                                                ) : null}
                                                            </div>
                                                        ) : (
                                                            <span className="text-xs font-semibold text-slate-500">Simpan dulu</span>
                                                        )
                                                    ) : (
                                                        <Badge variant={statusVariants[currentStatus]}>{statusLabels[currentStatus]}</Badge>
                                                    )}
                                                </Td>
                                            </Tr>
                                        );
                                    })}
                                </TBody>
                            </Table>
                        </TableContainer>

                        <div className="mt-4 flex justify-end">
                            <Button type="submit" disabled={processing}>
                                {processing ? <Loader2 size={17} className="animate-spin" /> : <Save size={17} />}
                                Simpan Absensi
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </AppLayout>
    );
}
