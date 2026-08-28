import * as React from 'react';
import { cn } from '@/lib/utils';

export function Checkbox({ className, ...props }: React.InputHTMLAttributes<HTMLInputElement>) {
    return (
        <input
            type="checkbox"
            className={cn(
                'h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
                className,
            )}
            {...props}
        />
    );
}
