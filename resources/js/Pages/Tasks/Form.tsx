import { Head, Link, router, useForm } from '@inertiajs/react';
import { ArrowLeft, Save, Trash2 } from 'lucide-react';
import { FormEvent, useState } from 'react';
import { ConfirmDeleteDialog } from '@/Components/ConfirmDeleteDialog';
import { ErrorSummary } from '@/Components/ErrorSummary';
import { FileUploadField } from '@/Components/FileUploadField';
import { FormField } from '@/Components/FormField';
import { ResourceForm } from '@/Components/ResourceForm';
import AppLayout from '@/Layouts/AppLayout';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Select } from '@/Components/ui/select';
import { Textarea } from '@/Components/ui/textarea';

type TaskStatus = 'antrean' | 'proses' | 'selesai';
type TaskPriority = 'rendah' | 'sedang' | 'tinggi';

interface UserOption {
    id: number;
    name: string;
    email: string;
}

interface TaskRecord {
    id: number;
    judul: string;
    deskripsi: string | null;
    assigned_to: number | null;
    prioritas: TaskPriority;
    deadline: string | null;
    status: TaskStatus;
    kategori: string | null;
    progress_persen: number;
    attachment_url: string | null;
}

interface TaskFormData {
    judul: string;
    deskripsi: string;
    assigned_to: string;
    prioritas: TaskPriority;
    deadline: string;
    status: TaskStatus;
    kategori: string;
    progress_persen: string;
    attachment: File | null;
}

interface TaskFormProps {
    task?: TaskRecord;
    users: UserOption[];
}

export default function TaskForm({ task, users }: TaskFormProps) {
    const isEdit = Boolean(task);
    const [confirmOpen, setConfirmOpen] = useState(false);
    const { data, setData, post, processing, errors, transform } = useForm<TaskFormData>({
        judul: task?.judul ?? '',
        deskripsi: task?.deskripsi ?? '',
        assigned_to: task?.assigned_to?.toString() ?? '',
        prioritas: task?.prioritas ?? 'sedang',
        deadline: task?.deadline ?? '',
        status: task?.status ?? 'antrean',
        kategori: task?.kategori ?? '',
        progress_persen: task?.progress_persen?.toString() ?? '0',
        attachment: null,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (task) {
            transform((data) => ({ ...data, _method: 'PUT' }));
            post(`/tasks/${task.id}`, { forceFormData: true });
        } else {
            transform((data) => data);
            post('/tasks', { forceFormData: true });
        }
    };

    const destroy = () => {
        if (!task) {
            return;
        }

        router.delete(`/tasks/${task.id}`, {
            onFinish: () => setConfirmOpen(false),
        });
    };

    return (
        <AppLayout title={isEdit ? 'Edit Tugas' : 'Tambah Tugas'}>
            <Head title={isEdit ? 'Edit Tugas' : 'Tambah Tugas'} />
            <div className="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <Link href="/tasks">
                    <Button variant="outline" size="sm">
                        <ArrowLeft size={16} />
                        Kembali
                    </Button>
                </Link>
                {task ? (
                    <Button variant="danger" size="sm" onClick={() => setConfirmOpen(true)}>
                        <Trash2 size={16} />
                        Hapus Tugas
                    </Button>
                ) : null}
            </div>

            <div className="grid gap-4 xl:grid-cols-[minmax(0,1fr)_20rem]">
                <ResourceForm
                    title={isEdit ? `Edit Tugas: ${task?.judul}` : 'Form Tambah Tugas'}
                    onSubmit={submit}
                    actions={
                        <>
                            <Link href="/tasks">
                                <Button type="button" variant="outline" disabled={processing}>Batal</Button>
                            </Link>
                            <Button type="submit" disabled={processing}>
                                <Save size={17} />
                                Simpan
                            </Button>
                        </>
                    }
                >
                    <ErrorSummary errors={errors} />
                    <FormField label="Judul Tugas" error={errors.judul}>
                        <Input value={data.judul} required onChange={(event) => setData('judul', event.target.value)} />
                    </FormField>
                    <FormField label="Deskripsi" error={errors.deskripsi}>
                        <Textarea value={data.deskripsi} onChange={(event) => setData('deskripsi', event.target.value)} />
                    </FormField>
                    <div className="grid gap-4 md:grid-cols-2">
                        <FormField label="Ditugaskan Kepada" error={errors.assigned_to}>
                            <Select value={data.assigned_to} onChange={(event) => setData('assigned_to', event.target.value)}>
                                <option value="">Pilih Staf/Guru</option>
                                {users.map((user) => (
                                    <option key={user.id} value={user.id}>
                                        {user.name} ({user.email})
                                    </option>
                                ))}
                            </Select>
                        </FormField>
                        <FormField label="Prioritas" error={errors.prioritas}>
                            <Select value={data.prioritas} onChange={(event) => setData('prioritas', event.target.value as TaskPriority)}>
                                <option value="rendah">Rendah</option>
                                <option value="sedang">Sedang</option>
                                <option value="tinggi">Tinggi</option>
                            </Select>
                        </FormField>
                    </div>
                    <div className="grid gap-4 md:grid-cols-3">
                        <FormField label="Deadline" error={errors.deadline}>
                            <Input type="date" value={data.deadline} onChange={(event) => setData('deadline', event.target.value)} />
                        </FormField>
                        <FormField label="Kategori" error={errors.kategori}>
                            <Input value={data.kategori} placeholder="Contoh: Administrasi" onChange={(event) => setData('kategori', event.target.value)} />
                        </FormField>
                        <FormField label="Status" error={errors.status}>
                            <Select value={data.status} onChange={(event) => setData('status', event.target.value as TaskStatus)}>
                                <option value="antrean">Antrean</option>
                                <option value="proses">Dalam Proses</option>
                                <option value="selesai">Selesai</option>
                            </Select>
                        </FormField>
                    </div>
                    <FormField label="Progress (%)" error={errors.progress_persen}>
                        <Input type="number" min={0} max={100} value={data.progress_persen} onChange={(event) => setData('progress_persen', event.target.value)} />
                    </FormField>
                    {task?.attachment_url ? (
                        <a href={task.attachment_url} target="_blank" rel="noreferrer" className="text-sm font-semibold text-blue-700">
                            Lihat lampiran saat ini
                        </a>
                    ) : null}
                    <FileUploadField label="Lampiran" name="attachment" hint="Maksimal 5MB." error={errors.attachment} onChange={(file) => setData('attachment', file)} />
                </ResourceForm>

                {task ? (
                    <Card>
                        <CardContent>
                            <div className="font-semibold text-rose-800">Zona Hapus</div>
                            <p className="mt-2 text-sm font-medium text-slate-600">Menghapus tugas akan menghapus semua log aktivitas terkait.</p>
                            <Button className="mt-4" variant="danger" size="sm" onClick={() => setConfirmOpen(true)}>
                                <Trash2 size={16} />
                                Hapus Tugas Ini
                            </Button>
                        </CardContent>
                    </Card>
                ) : null}
            </div>

            <ConfirmDeleteDialog open={confirmOpen} message={`Hapus tugas ${task?.judul ?? ''}?`} processing={processing} onClose={() => setConfirmOpen(false)} onConfirm={destroy} />
        </AppLayout>
    );
}
