import { AlertTriangle } from 'lucide-react';
import { Button } from '@/Components/ui/button';
import { Dialog } from '@/Components/ui/dialog';

interface ConfirmDeleteDialogProps {
    open: boolean;
    title?: string;
    message: string;
    processing?: boolean;
    onClose: () => void;
    onConfirm: () => void;
}

export function ConfirmDeleteDialog({
    open,
    title = 'Konfirmasi hapus',
    message,
    processing = false,
    onClose,
    onConfirm,
}: ConfirmDeleteDialogProps) {
    return (
        <Dialog open={open} title={title} onClose={onClose}>
            <div className="flex gap-3">
                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-rose-50 text-rose-700">
                    <AlertTriangle size={20} />
                </div>
                <p className="text-sm font-medium leading-6 text-slate-600">{message}</p>
            </div>
            <div className="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <Button type="button" variant="outline" onClick={onClose} disabled={processing}>
                    Batal
                </Button>
                <Button type="button" variant="danger" onClick={onConfirm} disabled={processing}>
                    Hapus
                </Button>
            </div>
        </Dialog>
    );
}
