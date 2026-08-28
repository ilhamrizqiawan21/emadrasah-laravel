import { Head, Link, router } from '@inertiajs/react';
import { CalendarDays, Edit, Eye, Loader2, Plus, Search, Trash2 } from 'lucide-react';
import { FormEvent, useMemo, useState } from 'react';
import { ConfirmDeleteDialog } from '@/Components/ConfirmDeleteDialog';
import AppLayout from '@/Layouts/AppLayout';
import { Pagination } from '@/Components/Pagination';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Select } from '@/Components/ui/select';
import { formatDate } from '@/lib/format';
import { Paginated } from '@/types';

type TaskStatus = 'antrean' | 'proses' | 'selesai';
type TaskPriority = 'rendah' | 'sedang' | 'tinggi';

interface UserOption {
    id: number;
    name: string;
    email: string;
}

interface TaskRow {
    id: number;
    judul: string;
    deskripsi: string | null;
    assigned_to: number | null;
    assigned_to_user: UserOption | null;
    prioritas: TaskPriority;
    deadline: string | null;
    status: TaskStatus;
    kategori: string | null;
    progress_persen: number;
    attachment_url: string | null;
    created_at: string | null;
    is_overdue: boolean;
}

interface TasksIndexProps {
    tasks: Paginated<TaskRow>;
    users: UserOption[];
    filters: {
        search?: string;
        status?: string;
        prioritas?: string;
        assigned_to?: string;
        sort?: string;
        direction?: string;
    };
}

const statuses: Record<TaskStatus, string> = {
    antrean: 'Antrean',
    proses: 'Dalam Proses',
    selesai: 'Selesai',
};

const statusVariants: Record<TaskStatus, 'default' | 'warning' | 'success'> = {
    antrean: 'default',
    proses: 'warning',
    selesai: 'success',
};

const priorityVariants: Record<TaskPriority, 'info' | 'warning' | 'danger'> = {
    rendah: 'info',
    sedang: 'warning',
    tinggi: 'danger',
};

export default function TasksIndex({ tasks, users, filters }: TasksIndexProps) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState(filters.status ?? '');
    const [prioritas, setPrioritas] = useState(filters.prioritas ?? '');
    const [assignedTo, setAssignedTo] = useState(filters.assigned_to ?? '');
    const [loading, setLoading] = useState(false);
    const [deleteTarget, setDeleteTarget] = useState<TaskRow | null>(null);

    const query = { search, status, prioritas, assigned_to: assignedTo };
    const grouped = useMemo(
        () => Object.keys(statuses).reduce<Record<TaskStatus, TaskRow[]>>((carry, key) => {
            carry[key as TaskStatus] = tasks.data.filter((task) => task.status === key);
            return carry;
        }, { antrean: [], proses: [], selesai: [] }),
        [tasks.data],
    );

    const applyFilters = (event?: FormEvent) => {
        event?.preventDefault();
        setLoading(true);
        router.get('/tasks', query, {
            preserveState: true,
            replace: true,
            onFinish: () => setLoading(false),
        });
    };

    const reset = () => router.get('/tasks');

    const changeStatus = (task: TaskRow, nextStatus: TaskStatus) => {
        router.patch(`/tasks/${task.id}/status`, { status: nextStatus }, { preserveScroll: true });
    };

    const destroy = () => {
        if (!deleteTarget) {
            return;
        }

        router.delete(`/tasks/${deleteTarget.id}`, {
            onFinish: () => setDeleteTarget(null),
        });
    };

    const hasFilters = Boolean(filters.search || filters.status || filters.prioritas || filters.assigned_to || filters.sort);

    return (
        <AppLayout title="Tugas TU">
            <Head title="Tugas TU" />
            <Card>
                <CardHeader className="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <CardTitle>Tugas TU</CardTitle>
                        <p className="mt-1 text-sm font-medium text-slate-500">Kelola antrean, prioritas, penanggung jawab, dan progres tugas.</p>
                    </div>
                    <Link href="/tasks/create">
                        <Button>
                            <Plus size={17} />
                            Tugas Baru
                        </Button>
                    </Link>
                </CardHeader>
                <CardContent>
                    <form className="mb-5 grid gap-2 xl:grid-cols-[minmax(220px,1fr)_160px_180px_220px_auto_auto]" onSubmit={applyFilters}>
                        <div className="flex min-w-0 items-center gap-2">
                            <Search size={18} className="shrink-0 text-slate-400" />
                            <Input type="search" value={search} placeholder="Cari judul atau deskripsi" onChange={(event) => setSearch(event.target.value)} />
                        </div>
                        <Select value={status} onChange={(event) => setStatus(event.target.value)} aria-label="Filter status">
                            <option value="">Semua Status</option>
                            {Object.entries(statuses).map(([value, label]) => <option key={value} value={value}>{label}</option>)}
                        </Select>
                        <Select value={prioritas} onChange={(event) => setPrioritas(event.target.value)} aria-label="Filter prioritas">
                            <option value="">Semua Prioritas</option>
                            <option value="rendah">Rendah</option>
                            <option value="sedang">Sedang</option>
                            <option value="tinggi">Tinggi</option>
                        </Select>
                        <Select value={assignedTo} onChange={(event) => setAssignedTo(event.target.value)} aria-label="Filter penanggung jawab">
                            <option value="">Semua Penanggung Jawab</option>
                            {users.map((user) => <option key={user.id} value={user.id}>{user.name}</option>)}
                        </Select>
                        <Button variant="outline" type="submit" disabled={loading}>
                            {loading ? <Loader2 size={16} className="animate-spin" /> : null}
                            Filter
                        </Button>
                        {hasFilters ? <Button variant="ghost" type="button" onClick={reset}>Reset</Button> : null}
                    </form>

                    <div className="grid gap-4 xl:grid-cols-3">
                        {(Object.keys(statuses) as TaskStatus[]).map((statusKey) => (
                            <section key={statusKey} className="rounded-lg border border-slate-200 bg-slate-50">
                                <div className="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                                    <div className="font-semibold text-slate-900">{statuses[statusKey]}</div>
                                    <Badge variant={statusVariants[statusKey]}>{grouped[statusKey].length}</Badge>
                                </div>
                                <div className="grid max-h-[70vh] gap-3 overflow-y-auto p-3">
                                    {grouped[statusKey].length ? grouped[statusKey].map((task) => (
                                        <article key={task.id} className={`rounded-md border bg-white p-4 shadow-sm ${task.is_overdue ? 'border-rose-200 bg-rose-50' : 'border-slate-200'}`}>
                                            <div className="flex items-start justify-between gap-3">
                                                <div className="min-w-0">
                                                    <h3 className="font-semibold text-slate-950">{task.judul}</h3>
                                                    {task.kategori ? <div className="mt-1 text-xs font-semibold text-slate-500">{task.kategori}</div> : null}
                                                </div>
                                                <Badge variant={priorityVariants[task.prioritas]}>{task.prioritas}</Badge>
                                            </div>
                                            <div className="mt-3 text-sm font-medium text-slate-600">{task.assigned_to_user?.name ?? 'Tidak ditugaskan'}</div>
                                            {task.deadline ? (
                                                <div className={`mt-2 flex items-center gap-2 text-sm font-semibold ${task.is_overdue ? 'text-rose-700' : 'text-slate-500'}`}>
                                                    <CalendarDays size={15} />
                                                    {formatDate(task.deadline)}
                                                </div>
                                            ) : null}
                                            <div className="mt-3 h-2 overflow-hidden rounded-full bg-slate-200">
                                                <div className="h-full rounded-full bg-blue-600" style={{ width: `${task.progress_persen}%` }} />
                                            </div>
                                            <div className="mt-3 flex flex-wrap items-center justify-between gap-2">
                                                <Select className="h-8 max-w-36 text-xs" value={task.status} onChange={(event) => changeStatus(task, event.target.value as TaskStatus)} aria-label={`Ubah status ${task.judul}`}>
                                                    {Object.entries(statuses).map(([value, label]) => <option key={value} value={value}>{label}</option>)}
                                                </Select>
                                                <div className="flex gap-2">
                                                    <Link href={`/tasks/${task.id}`}>
                                                        <Button variant="outline" size="icon" title="Detail" aria-label={`Detail tugas ${task.judul}`}>
                                                            <Eye size={16} />
                                                        </Button>
                                                    </Link>
                                                    <Link href={`/tasks/${task.id}/edit`}>
                                                        <Button variant="outline" size="icon" title="Edit" aria-label={`Edit tugas ${task.judul}`}>
                                                            <Edit size={16} />
                                                        </Button>
                                                    </Link>
                                                    <Button variant="danger" size="icon" title="Hapus" aria-label={`Hapus tugas ${task.judul}`} onClick={() => setDeleteTarget(task)}>
                                                        <Trash2 size={16} />
                                                    </Button>
                                                </div>
                                            </div>
                                        </article>
                                    )) : (
                                        <div className="rounded-md border border-dashed border-slate-300 px-4 py-8 text-center text-sm font-semibold text-slate-500">Tidak ada tugas</div>
                                    )}
                                </div>
                            </section>
                        ))}
                    </div>

                    <div className="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="text-sm font-medium text-slate-500">
                            Menampilkan {tasks.from ?? 0}-{tasks.to ?? 0} dari {tasks.total} tugas
                        </div>
                        <Pagination links={tasks.links} />
                    </div>
                </CardContent>
            </Card>

            <ConfirmDeleteDialog open={Boolean(deleteTarget)} message={`Hapus tugas ${deleteTarget?.judul ?? ''}? Log aktivitas terkait ikut terhapus.`} onClose={() => setDeleteTarget(null)} onConfirm={destroy} />
        </AppLayout>
    );
}
