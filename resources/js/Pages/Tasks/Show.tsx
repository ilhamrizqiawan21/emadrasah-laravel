import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, CalendarDays, Edit, FileText } from 'lucide-react';
import AppLayout from '@/Layouts/AppLayout';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { formatDate } from '@/lib/format';

type TaskStatus = 'antrean' | 'proses' | 'selesai';
type TaskPriority = 'rendah' | 'sedang' | 'tinggi';

interface UserRecord {
    id: number;
    name: string;
    email?: string;
}

interface TaskRecord {
    id: number;
    judul: string;
    deskripsi: string | null;
    assigned_to_user: UserRecord | null;
    creator: UserRecord | null;
    prioritas: TaskPriority;
    deadline: string | null;
    status: TaskStatus;
    kategori: string | null;
    progress_persen: number;
    attachment_url: string | null;
    created_at: string | null;
    updated_at: string | null;
    is_overdue: boolean;
}

interface TaskLogRecord {
    id: number;
    action: string;
    keterangan: string | null;
    created_at: string | null;
    user: UserRecord | null;
}

interface TaskShowProps {
    task: TaskRecord;
    logs: TaskLogRecord[];
}

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

export default function TaskShow({ task, logs }: TaskShowProps) {
    return (
        <AppLayout title="Detail Tugas">
            <Head title="Detail Tugas" />
            <div className="mb-4 flex flex-wrap gap-2">
                <Link href="/tasks">
                    <Button variant="outline">
                        <ArrowLeft size={17} />
                        Kembali
                    </Button>
                </Link>
                <Link href={`/tasks/${task.id}/edit`}>
                    <Button variant="outline">
                        <Edit size={17} />
                        Edit
                    </Button>
                </Link>
            </div>

            <div className="grid gap-4 xl:grid-cols-[minmax(0,1fr)_26rem]">
                <Card>
                    <CardHeader>
                        <CardTitle>{task.judul}</CardTitle>
                        {task.kategori ? <p className="mt-1 text-sm font-semibold text-slate-500">{task.kategori}</p> : null}
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-3">
                            <div>
                                <div className="text-xs font-bold uppercase text-slate-500">Status</div>
                                <div className="mt-2"><Badge variant={statusVariants[task.status]}>{task.status}</Badge></div>
                            </div>
                            <div>
                                <div className="text-xs font-bold uppercase text-slate-500">Prioritas</div>
                                <div className="mt-2"><Badge variant={priorityVariants[task.prioritas]}>{task.prioritas}</Badge></div>
                            </div>
                            <div>
                                <div className="text-xs font-bold uppercase text-slate-500">Deadline</div>
                                <div className={`mt-2 flex items-center gap-2 text-sm font-semibold ${task.is_overdue ? 'text-rose-700' : 'text-slate-700'}`}>
                                    <CalendarDays size={16} />
                                    {formatDate(task.deadline)}
                                </div>
                            </div>
                        </div>

                        <div className="mt-6">
                            <div className="text-xs font-bold uppercase text-slate-500">Ditugaskan Kepada</div>
                            <div className="mt-2 font-semibold text-slate-900">{task.assigned_to_user?.name ?? 'Tidak ditugaskan'}</div>
                        </div>

                        <div className="mt-6">
                            <div className="text-xs font-bold uppercase text-slate-500">Deskripsi</div>
                            <div className="mt-2 rounded-md bg-slate-50 p-4 text-sm font-medium text-slate-700">{task.deskripsi ?? 'Tidak ada deskripsi.'}</div>
                        </div>

                        <div className="mt-6">
                            <div className="mb-2 flex items-center justify-between text-xs font-bold uppercase text-slate-500">
                                <span>Progress</span>
                                <span>{task.progress_persen}%</span>
                            </div>
                            <div className="h-2 overflow-hidden rounded-full bg-slate-200">
                                <div className="h-full rounded-full bg-blue-600" style={{ width: `${task.progress_persen}%` }} />
                            </div>
                        </div>

                        {task.attachment_url ? (
                            <a href={task.attachment_url} target="_blank" rel="noreferrer" className="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-blue-700">
                                <FileText size={16} />
                                Lihat lampiran
                            </a>
                        ) : null}

                        <div className="mt-6 text-sm font-medium text-slate-500">
                            Dibuat: {task.created_at ?? '-'}<br />
                            Terakhir diperbarui: {task.updated_at ?? '-'}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Log Aktivitas</CardTitle>
                    </CardHeader>
                    <CardContent className="max-h-[32rem] overflow-y-auto">
                        {logs.length ? (
                            <div className="grid gap-3">
                                {logs.map((log) => (
                                    <div key={log.id} className="rounded-md border border-slate-200 p-3">
                                        <div className="flex items-start justify-between gap-3">
                                            <div className="font-semibold text-slate-900">{log.action}</div>
                                            <div className="shrink-0 text-xs font-medium text-slate-500">{log.created_at ?? '-'}</div>
                                        </div>
                                        <div className="mt-1 text-xs font-semibold text-slate-500">{log.user?.name ?? 'Sistem'}</div>
                                        {log.keterangan ? <div className="mt-2 text-sm font-medium text-slate-600">{log.keterangan}</div> : null}
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="rounded-md border border-dashed border-slate-300 px-4 py-10 text-center text-sm font-semibold text-slate-500">
                                Belum ada aktivitas.
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
