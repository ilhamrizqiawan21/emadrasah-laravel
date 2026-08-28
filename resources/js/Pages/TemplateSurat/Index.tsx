import { Head, Link, router } from '@inertiajs/react';
import { Edit, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { ConfirmDeleteDialog } from '@/Components/ConfirmDeleteDialog';
import { DataTable, DataTableColumn } from '@/Components/DataTable';
import AppLayout from '@/Layouts/AppLayout';
import { Pagination } from '@/Components/Pagination';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Paginated } from '@/types';

interface TemplateRow {
    id: number;
    nama_template: string;
    konten: string;
    preview: string;
}

interface TemplateIndexProps {
    templates: Paginated<TemplateRow>;
}

export default function TemplateSuratIndex({ templates }: TemplateIndexProps) {
    const [deleteTarget, setDeleteTarget] = useState<TemplateRow | null>(null);

    const destroy = () => {
        if (!deleteTarget) {
            return;
        }

        router.delete(`/template-surat/${deleteTarget.id}`, {
            onFinish: () => setDeleteTarget(null),
        });
    };

    const columns: Array<DataTableColumn<TemplateRow>> = [
        {
            key: 'nama_template',
            label: 'Nama Template',
            render: (item) => <span className="font-semibold text-slate-900">{item.nama_template}</span>,
        },
        {
            key: 'preview',
            label: 'Konten Preview',
            render: (item) => <span className="line-clamp-2 font-medium text-slate-600">{item.preview || '-'}</span>,
        },
        {
            key: 'actions',
            label: 'Aksi',
            headerClassName: 'w-28 text-right',
            className: 'text-right',
            render: (item) => (
                <div className="flex justify-end gap-2">
                    <Link href={`/template-surat/${item.id}/edit`}>
                        <Button variant="outline" size="icon" title="Edit" aria-label={`Edit template ${item.nama_template}`}>
                            <Edit size={16} />
                        </Button>
                    </Link>
                    <Button variant="danger" size="icon" title="Hapus" aria-label={`Hapus template ${item.nama_template}`} onClick={() => setDeleteTarget(item)}>
                        <Trash2 size={16} />
                    </Button>
                </div>
            ),
        },
    ];

    return (
        <AppLayout title="Template Surat">
            <Head title="Template Surat" />
            <Card>
                <CardHeader className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <CardTitle>Template Surat</CardTitle>
                        <p className="mt-1 text-sm font-medium text-slate-500">Kelola format konten surat yang sering dipakai.</p>
                    </div>
                    <Link href="/template-surat/create">
                        <Button>
                            <Plus size={17} />
                            Tambah Template
                        </Button>
                    </Link>
                </CardHeader>
                <CardContent>
                    <DataTable
                        data={templates.data}
                        columns={columns}
                        emptyTitle="Belum ada template surat"
                        emptyDescription="Template baru akan tampil di daftar ini."
                    />
                    <div className="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="text-sm font-medium text-slate-500">
                            Menampilkan {templates.from ?? 0}-{templates.to ?? 0} dari {templates.total} template
                        </div>
                        <Pagination links={templates.links} />
                    </div>
                </CardContent>
            </Card>

            <ConfirmDeleteDialog open={Boolean(deleteTarget)} message={`Hapus template ${deleteTarget?.nama_template ?? ''}?`} onClose={() => setDeleteTarget(null)} onConfirm={destroy} />
        </AppLayout>
    );
}
