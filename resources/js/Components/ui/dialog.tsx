import * as React from 'react';
import { X } from 'lucide-react';
import { Button } from '@/Components/ui/button';
import { cn } from '@/lib/utils';

interface DialogProps {
    open: boolean;
    title: string;
    description?: string;
    children: React.ReactNode;
    onClose: () => void;
}

export function Dialog({ open, title, description, children, onClose }: DialogProps) {
    if (!open) {
        return null;
    }

    return (
        <div className="fixed inset-0 z-50 grid place-items-center bg-slate-950/45 p-4" role="presentation" onMouseDown={onClose}>
            <section
                role="dialog"
                aria-modal="true"
                aria-labelledby="dialog-title"
                aria-describedby={description ? 'dialog-description' : undefined}
                className="w-full max-w-md rounded-lg border border-slate-200 bg-white shadow-xl"
                onMouseDown={(event) => event.stopPropagation()}
            >
                <div className="flex items-start justify-between gap-4 border-b border-slate-200 p-5">
                    <div>
                        <h2 id="dialog-title" className="font-display text-base font-bold text-slate-950">
                            {title}
                        </h2>
                        {description ? (
                            <p id="dialog-description" className="mt-1 text-sm font-medium text-slate-500">
                                {description}
                            </p>
                        ) : null}
                    </div>
                    <Button type="button" variant="ghost" size="icon" onClick={onClose} aria-label="Tutup dialog">
                        <X size={18} />
                    </Button>
                </div>
                <div className={cn('p-5')}>{children}</div>
            </section>
        </div>
    );
}
