import { ReactNode } from 'react';

interface FormFieldProps {
    label: string;
    error?: string;
    children: ReactNode;
    hint?: string;
}

export function FormField({ label, error, children, hint }: FormFieldProps) {
    return (
        <label className="grid gap-2 text-sm font-semibold text-slate-700">
            <span>{label}</span>
            {children}
            {hint ? <span className="text-xs font-medium text-slate-500">{hint}</span> : null}
            {error ? <span className="text-xs font-medium text-rose-600">{error}</span> : null}
        </label>
    );
}
