import { Head } from '@inertiajs/react';
import {
    AlertTriangle,
    Boxes,
    CalendarCheck,
    CheckCircle2,
    ClipboardCheck,
    FilePlus2,
    FileText,
    Gauge,
    GraduationCap,
    Plus,
    School,
    Users,
} from 'lucide-react';
import AppLayout from '@/Layouts/AppLayout';
import { Badge } from '@/Components/ui/badge';
import { buttonVariants } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { cn } from '@/lib/utils';
import { UserRole } from '@/types';

interface DashboardProps {
    role: UserRole;
    stats: {
        total_siswa: number | null;
        total_guru: number | null;
        total_kelas: number | null;
        task_pending: number | null;
        surat_masuk_bulan_ini: number | null;
        guru_hadir_hari_ini: number | null;
        sarana_rusak: number | null;
        persen_buku_induk_lengkap: number | null;
    };
    todayLabel: string;
    kehadiran: Array<{ tanggal: string; hadir: number; tidak_hadir: number }>;
    jadwalHariIni: Array<{ id: number; kelas: string; mapel: string; guru: string; jam: string; ruang: string | null }>;
    pendingTasks: Array<{ id: number; judul: string; prioritas: string; deadline: string | null; status: string; progress_persen: number }>;
    recentSuratMasuk: Array<{ id: number; nomor_agenda: string; asal_surat: string; perihal: string; tanggal_terima: string; status: string }>;
    saranaBermasalah: Array<{ id: number; kode_sarana: string; nama_sarana: string; kondisi: string; stok_tersedia: number; lokasi_ruang: string | null }>;
    attentionItems: Array<{ label: string; value: number; href: string }>;
    quickActions: Array<{ label: string; href: string; variant: 'default' | 'secondary' | 'outline' }>;
}

const roleLabels: Record<UserRole, string> = {
    admin: 'Admin',
    operator: 'Operator',
    guru: 'Guru',
    siswa: 'Siswa',
    wali_murid: 'Wali Murid',
};

const quickActionIcons = [Plus, ClipboardCheck, CheckCircle2, FilePlus2, Boxes, CalendarCheck, GraduationCap];

function formatDate(value: string | null) {
    if (!value) {
        return 'Tanpa deadline';
    }

    return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(value));
}

function badgeVariant(value: string) {
    if (['tinggi', 'rusak_berat', 'diproses'].includes(value)) {
        return 'danger' as const;
    }

    if (['sedang', 'rusak_ringan', 'diterima'].includes(value)) {
        return 'warning' as const;
    }

    return 'default' as const;
}

export default function DashboardIndex({
    role,
    stats,
    todayLabel,
    kehadiran,
    jadwalHariIni,
    pendingTasks,
    recentSuratMasuk,
    saranaBermasalah,
    attentionItems,
    quickActions,
}: DashboardProps) {
    const statCards = [
        { label: 'Siswa', value: stats.total_siswa, icon: Users, tone: 'bg-sky-50 text-sky-700' },
        { label: 'Guru', value: stats.total_guru, icon: GraduationCap, tone: 'bg-emerald-50 text-emerald-700' },
        { label: 'Kelas', value: stats.total_kelas, icon: School, tone: 'bg-indigo-50 text-indigo-700' },
        { label: 'Task Aktif', value: stats.task_pending, icon: CheckCircle2, tone: 'bg-amber-50 text-amber-700' },
        { label: 'Surat Bulan Ini', value: stats.surat_masuk_bulan_ini, icon: FileText, tone: 'bg-blue-50 text-blue-700' },
        { label: 'Guru Hadir', value: stats.guru_hadir_hari_ini, icon: CalendarCheck, tone: 'bg-teal-50 text-teal-700' },
        { label: 'Sarana Rusak', value: stats.sarana_rusak, icon: Boxes, tone: 'bg-rose-50 text-rose-700' },
    ].filter((stat) => stat.value !== null);

    return (
        <AppLayout title="Dashboard">
            <Head title="Dashboard" />
            <div className="mb-6 flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                <div>
                    <Badge variant="info">{roleLabels[role]}</Badge>
                    <p className="mt-2 max-w-2xl text-sm font-medium leading-6 text-slate-600">
                        Ringkasan kerja hari ini untuk memantau jadwal, administrasi, dan data yang butuh perhatian.
                    </p>
                </div>
                {quickActions.length ? (
                    <div className="flex flex-wrap gap-2">
                        {quickActions.map((action, index) => {
                            const Icon = quickActionIcons[index] ?? Plus;

                            return (
                                <a
                                    key={action.href}
                                    href={action.href}
                                    className={cn(buttonVariants({ variant: action.variant, size: 'sm' }), 'shrink-0')}
                                >
                                    <Icon size={15} />
                                    {action.label}
                                </a>
                            );
                        })}
                    </div>
                ) : null}
            </div>

            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                {statCards.map((stat) => {
                    const Icon = stat.icon;

                    return (
                        <Card key={stat.label}>
                            <CardContent className="flex items-center justify-between">
                                <div className="min-w-0">
                                    <div className="text-sm font-semibold text-slate-500">{stat.label}</div>
                                    <div className="mt-2 font-display text-3xl font-extrabold text-slate-950">{stat.value}</div>
                                </div>
                                <div className={cn('flex h-12 w-12 shrink-0 items-center justify-center rounded-md', stat.tone)}>
                                    <Icon size={23} />
                                </div>
                            </CardContent>
                        </Card>
                    );
                })}
            </div>

            <div className="mt-6 grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between gap-3">
                        <CardTitle>Jadwal Hari Ini</CardTitle>
                        <Badge variant="default">{todayLabel}</Badge>
                    </CardHeader>
                    <CardContent className="grid gap-3">
                        {jadwalHariIni.length ? (
                            jadwalHariIni.map((jadwal) => (
                                <div key={jadwal.id} className="grid gap-2 rounded-md border border-slate-200 p-3 sm:grid-cols-[7.5rem_1fr_auto] sm:items-center">
                                    <div className="text-sm font-extrabold text-slate-950">{jadwal.jam}</div>
                                    <div className="min-w-0">
                                        <div className="truncate font-semibold text-slate-900">{jadwal.mapel}</div>
                                        <div className="text-xs font-medium text-slate-500">
                                            {jadwal.kelas} - {jadwal.guru}
                                        </div>
                                    </div>
                                    <Badge variant="info">{jadwal.ruang ?? 'Ruang belum diisi'}</Badge>
                                </div>
                            ))
                        ) : (
                            <div className="rounded-md border border-dashed border-slate-300 p-6 text-center text-sm font-semibold text-slate-500">
                                Tidak ada jadwal aktif hari ini.
                            </div>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Perlu Dilengkapi</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-3">
                        {stats.persen_buku_induk_lengkap !== null ? (
                            <div className="rounded-md border border-slate-200 p-3">
                                <div className="flex items-center justify-between gap-3">
                                    <div className="text-sm font-semibold text-slate-600">Kelengkapan Buku Induk</div>
                                    <Gauge size={18} className="text-blue-700" />
                                </div>
                                <div className="mt-2 font-display text-4xl font-extrabold text-slate-950">
                                    {stats.persen_buku_induk_lengkap}%
                                </div>
                            </div>
                        ) : null}
                        {attentionItems.length ? (
                            attentionItems.map((item) => (
                                <a
                                    key={item.label}
                                    href={item.href}
                                    className="flex items-center justify-between gap-3 rounded-md border border-slate-200 p-3 transition hover:bg-slate-50"
                                >
                                    <span className="text-sm font-semibold text-slate-700">{item.label}</span>
                                    <Badge variant={item.value > 0 ? 'warning' : 'success'}>{item.value}</Badge>
                                </a>
                            ))
                        ) : (
                            <div className="rounded-md border border-dashed border-slate-300 p-6 text-center text-sm font-semibold text-slate-500">
                                Tidak ada indikator data untuk role ini.
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>

            <div className="mt-6 grid gap-6 xl:grid-cols-2">
                {kehadiran.length ? (
                    <Card>
                        <CardHeader>
                            <CardTitle>Kehadiran Guru 7 Hari</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-3">
                            {kehadiran.map((item) => {
                                const total = item.hadir + item.tidak_hadir;
                                const percent = total > 0 ? Math.round((item.hadir / total) * 100) : 0;

                                return (
                                    <div key={item.tanggal} className="grid gap-2">
                                        <div className="flex items-center justify-between text-sm">
                                            <span className="font-semibold text-slate-700">{item.tanggal}</span>
                                            <span className="text-slate-500">{item.hadir} hadir</span>
                                        </div>
                                        <div className="h-2 overflow-hidden rounded-full bg-slate-100">
                                            <div className="h-full bg-emerald-500" style={{ width: `${percent}%` }} />
                                        </div>
                                    </div>
                                );
                            })}
                        </CardContent>
                    </Card>
                ) : null}

                {pendingTasks.length ? (
                    <Card>
                        <CardHeader>
                            <CardTitle>Tugas Pending</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-3">
                            {pendingTasks.map((task) => (
                                <a
                                    key={task.id}
                                    href={`/tasks/${task.id}`}
                                    className="flex items-center justify-between gap-3 rounded-md border border-slate-200 p-3 transition hover:bg-slate-50"
                                >
                                    <div className="min-w-0">
                                        <div className="truncate font-semibold text-slate-900">{task.judul}</div>
                                        <div className="text-xs font-medium text-slate-500">{formatDate(task.deadline)}</div>
                                    </div>
                                    <Badge variant={badgeVariant(task.prioritas)}>{task.prioritas}</Badge>
                                </a>
                            ))}
                        </CardContent>
                    </Card>
                ) : null}

                {recentSuratMasuk.length ? (
                    <Card>
                        <CardHeader>
                            <CardTitle>Surat Masuk Terbaru</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-3">
                            {recentSuratMasuk.map((surat) => (
                                <a key={surat.id} href={`/surat-masuk/${surat.id}`} className="rounded-md border border-slate-200 p-3 transition hover:bg-slate-50">
                                    <div className="flex items-center justify-between gap-3">
                                        <div className="min-w-0 truncate font-semibold text-slate-900">{surat.perihal}</div>
                                        <Badge variant="info">{surat.nomor_agenda}</Badge>
                                    </div>
                                    <div className="mt-1 text-xs font-medium text-slate-500">{surat.asal_surat}</div>
                                </a>
                            ))}
                        </CardContent>
                    </Card>
                ) : null}

                {saranaBermasalah.length ? (
                    <Card>
                        <CardHeader className="flex flex-row items-center gap-2">
                            <AlertTriangle size={18} className="text-rose-600" />
                            <CardTitle>Sarana Bermasalah</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-3">
                            {saranaBermasalah.map((sarana) => (
                                <a key={sarana.id} href={`/sarana/${sarana.id}`} className="rounded-md border border-slate-200 p-3 transition hover:bg-slate-50">
                                    <div className="flex items-center justify-between gap-3">
                                        <div className="min-w-0 truncate font-semibold text-slate-900">{sarana.nama_sarana}</div>
                                        <Badge variant={badgeVariant(sarana.kondisi)}>{sarana.kondisi.replace('_', ' ')}</Badge>
                                    </div>
                                    <div className="mt-1 text-xs font-medium text-slate-500">
                                        {sarana.kode_sarana} - {sarana.lokasi_ruang ?? 'Lokasi belum diisi'} - Stok {sarana.stok_tersedia}
                                    </div>
                                </a>
                            ))}
                        </CardContent>
                    </Card>
                ) : null}
            </div>
        </AppLayout>
    );
}
