import { Head, Link, router } from '@inertiajs/react';
import { CalendarDays, Clock, Edit, Loader2, Plus, Search, Trash2 } from 'lucide-react';
import { FormEvent, useState } from 'react';
import { ConfirmDeleteDialog } from '@/Components/ConfirmDeleteDialog';
import { DataTable, DataTableColumn } from '@/Components/DataTable';
import AppLayout from '@/Layouts/AppLayout';
import { Pagination } from '@/Components/Pagination';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { formatNumber } from '@/lib/format';
import { Paginated } from '@/types';

interface JamPelajaranRow {
    id: number;
    hari: string;
    sesi_ke: number;
    jam_mulai: string;
    jam_selesai: string;
    durasi_menit: number;
}

interface JamPelajaranIndexProps {
    jamPelajaran: Paginated<JamPelajaranRow>;
    filters: {
        search?: string;
        sort?: string;
        direction?: string;
    };
    summary: {
        total_sesi: number;
        senin: number;
        jumat: number;
        rata_rata: number;
    };
}

const dayVariants: Record<string, 'default' | 'success' | 'warning' | 'danger' | 'info'> = {
    Senin: 'info',
    Selasa: 'success',
    Rabu: 'default',
    Kamis: 'warning',
    Jumat: 'danger',
    Sabtu: 'default',
};

export default function JamPelajaranIndex({ jamPelajaran, filters, summary }: JamPelajaranIndexProps) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [loading, setLoading] = useState(false);
    const [selectedJam, setSelectedJam] = useState<JamPelajaranRow | null>(null);

    const applyFilters = (event?: FormEvent) => {
        event?.preventDefault();
        setLoading(true);
        router.get('/jam-pelajaran', { search }, {
            preserveState: true,
            replace: true,
            onFinish: () => setLoading(false),
        });
    };

    const sortBy = (column: string) => {
        const direction = filters.sort === column && filters.direction === 'asc' ? 'desc' : 'asc';
        setLoading(true);
        router.get('/jam-pelajaran', { search, sort: column, direction }, {
            preserveState: true,
            replace: true,
            onFinish: () => setLoading(false),
        });
    };

    const destroy = () => {
        if (!selectedJam) {
            return;
        }

        router.delete(`/jam-pelajaran/${selectedJam.id}`, {
            onFinish: () => setSelectedJam(null),
        });
    };

    const statCards = [
        { label: 'Total Sesi', value: summary.total_sesi, icon: CalendarDays, tone: 'bg-blue-50 text-blue-700' },
        { label: 'Senin', value: summary.senin, icon: Clock, tone: 'bg-sky-50 text-sky-700' },
        { label: 'Jumat', value: summary.jumat, icon: Clock, tone: 'bg-rose-50 text-rose-700' },
        { label: 'Rata-rata', value: summary.rata_rata, icon: Clock, tone: 'bg-emerald-50 text-emerald-700' },
    ];

    const columns: Array<DataTableColumn<JamPelajaranRow>> = [
        {
            key: 'hari',
            label: 'Hari',
            sortable: true,
            render: (item) => <Badge variant={dayVariants[item.hari] ?? 'default'}>{item.hari}</Badge>,
        },
        {
            key: 'sesi_ke',
            label: 'Sesi Ke',
            sortable: true,
            render: (item) => <span className="font-semibold text-slate-900">{item.sesi_ke}</span>,
        },
        {
            key: 'jam_mulai',
            label: 'Jam Mulai',
            sortable: true,
            render: (item) => item.jam_mulai,
        },
        {
            key: 'jam_selesai',
            label: 'Jam Selesai',
            sortable: true,
            render: (item) => item.jam_selesai,
        },
        {
            key: 'durasi_menit',
            label: 'Durasi',
            render: (item) => `${formatNumber(item.durasi_menit)} menit`,
        },
        {
            key: 'actions',
            label: 'Aksi',
            headerClassName: 'w-32 text-right',
            className: 'text-right',
            render: (item) => (
                <div className="flex justify-end gap-2">
                    <Link href={`/jam-pelajaran/${item.id}/edit`}>
                        <Button variant="outline" size="icon" title="Edit" aria-label={`Edit ${item.hari} sesi ${item.sesi_ke}`}>
                            <Edit size={16} />
                        </Button>
                    </Link>
                    <Button variant="danger" size="icon" title="Hapus" aria-label={`Hapus ${item.hari} sesi ${item.sesi_ke}`} onClick={() => setSelectedJam(item)}>
                        <Trash2 size={16} />
                    </Button>
                </div>
            ),
        },
    ];

    return (
        <AppLayout title="Jam Pelajaran">
            <Head title="Jam Pelajaran" />
            <div className="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                {statCards.map((stat) => {
                    const Icon = stat.icon;

                    return (
                        <Card key={stat.label}>
                            <CardContent className="flex items-center justify-between">
                                <div>
                                    <div className="text-sm font-semibold text-slate-500">{stat.label}</div>
                                    <div className="mt-2 font-display text-3xl font-extrabold text-slate-950">{formatNumber(stat.value)}</div>
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
                <CardHeader className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <CardTitle>Daftar Sesi Pelajaran</CardTitle>
                        <p className="mt-1 text-sm font-medium text-slate-500">Kelola sesi dan waktu pelajaran per hari.</p>
                    </div>
                    <Link href="/jam-pelajaran/create">
                        <Button>
                            <Plus size={17} />
                            Tambah Jam
                        </Button>
                    </Link>
                </CardHeader>
                <CardContent>
                    <form className="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center" onSubmit={applyFilters}>
                        <div className="flex min-w-0 items-center gap-2 sm:min-w-96">
                            <Search size={18} className="text-slate-400" />
                            <Input
                                type="search"
                                value={search}
                                placeholder="Cari hari atau nomor sesi"
                                onChange={(event) => setSearch(event.target.value)}
                            />
                        </div>
                        <Button variant="outline" type="submit" disabled={loading}>
                            {loading ? <Loader2 size={16} className="animate-spin" /> : null}
                            Cari
                        </Button>
                        {filters.search ? (
                            <Button variant="ghost" type="button" onClick={() => router.get('/jam-pelajaran')}>
                                Reset
                            </Button>
                        ) : null}
                    </form>

                    <DataTable
                        data={jamPelajaran.data}
                        columns={columns}
                        loading={loading}
                        sort={filters.sort}
                        direction={filters.direction}
                        onSort={sortBy}
                        emptyTitle="Belum ada jam pelajaran"
                        emptyDescription="Sesi pelajaran yang cocok dengan filter akan tampil di sini."
                    />

                    <div className="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="text-sm font-medium text-slate-500">
                            Menampilkan {jamPelajaran.from ?? 0}-{jamPelajaran.to ?? 0} dari {jamPelajaran.total} sesi
                        </div>
                        <Pagination links={jamPelajaran.links} />
                    </div>
                </CardContent>
            </Card>

            <ConfirmDeleteDialog
                open={Boolean(selectedJam)}
                message={`Hapus jadwal ${selectedJam?.hari ?? ''} sesi ${selectedJam?.sesi_ke ?? ''}? Data ini akan dihapus permanen.`}
                onClose={() => setSelectedJam(null)}
                onConfirm={destroy}
            />
        </AppLayout>
    );
}
