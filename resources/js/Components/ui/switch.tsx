import * as React from 'react';
import { cn } from '@/lib/utils';

interface SwitchProps extends Omit<React.ButtonHTMLAttributes<HTMLButtonElement>, 'onChange'> {
    checked: boolean;
    onCheckedChange?: (checked: boolean) => void;
}

export function Switch({ checked, onCheckedChange, className, disabled, ...props }: SwitchProps) {
    return (
        <button
            type="button"
            role="switch"
            aria-checked={checked}
            disabled={disabled}
            className={cn(
                'inline-flex h-6 w-11 items-center rounded-full border border-transparent transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
                checked ? 'bg-blue-600' : 'bg-slate-300',
                className,
            )}
            onClick={() => onCheckedChange?.(!checked)}
            {...props}
        >
            <span
                className={cn(
                    'h-5 w-5 rounded-full bg-white shadow-sm transition-transform',
                    checked ? 'translate-x-5' : 'translate-x-0.5',
                )}
            />
        </button>
    );
}
