import { cn } from '@/lib/utils';

interface ToastProps {
    message?: string;
    variant?: 'success' | 'error' | 'info';
}

const variants = {
    success: 'border-emerald-200 bg-emerald-50 text-emerald-800',
    error: 'border-rose-200 bg-rose-50 text-rose-800',
    info: 'border-blue-200 bg-blue-50 text-blue-800',
};

export function Toast({ message, variant = 'info' }: ToastProps) {
    if (!message) {
        return null;
    }

    return <div className={cn('rounded-md border px-4 py-3 text-sm font-semibold', variants[variant])}>{message}</div>;
}
