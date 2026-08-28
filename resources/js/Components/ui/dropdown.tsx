import * as React from 'react';
import { ChevronDown } from 'lucide-react';
import { Button } from '@/Components/ui/button';
import { cn } from '@/lib/utils';

interface DropdownProps {
    label: string;
    children: React.ReactNode;
    className?: string;
}

export function Dropdown({ label, children, className }: DropdownProps) {
    const [open, setOpen] = React.useState(false);

    return (
        <div className={cn('relative inline-block text-left', className)}>
            <Button type="button" variant="outline" onClick={() => setOpen((current) => !current)} aria-expanded={open}>
                {label}
                <ChevronDown size={16} />
            </Button>
            {open ? (
                <div className="absolute right-0 z-20 mt-2 min-w-44 rounded-md border border-slate-200 bg-white p-1 shadow-lg">
                    {children}
                </div>
            ) : null}
        </div>
    );
}

export function DropdownItem({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) {
    return <div className={cn('rounded px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100', className)} {...props} />;
}
