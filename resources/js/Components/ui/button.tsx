import * as React from 'react';
import { cva, type VariantProps } from 'class-variance-authority';
import { cn } from '@/lib/utils';

const buttonVariants = cva(
    'inline-flex h-10 max-w-full items-center justify-center gap-2 rounded-md px-4 text-sm font-semibold leading-tight transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:pointer-events-none disabled:opacity-50',
    {
        variants: {
            variant: {
                default: 'bg-blue-600 text-white hover:bg-blue-700',
                secondary: 'bg-slate-100 text-slate-900 hover:bg-slate-200',
                outline: 'border border-slate-300 bg-white text-slate-800 hover:bg-slate-50',
                danger: 'bg-rose-600 text-white hover:bg-rose-700',
                ghost: 'text-slate-700 hover:bg-slate-100',
            },
            size: {
                default: 'h-10 px-4',
                sm: 'h-8 px-3 text-xs',
                icon: 'h-9 w-9 px-0',
            },
        },
        defaultVariants: {
            variant: 'default',
            size: 'default',
        },
    },
);

export interface ButtonProps
    extends React.ButtonHTMLAttributes<HTMLButtonElement>,
        VariantProps<typeof buttonVariants> {}

export function Button({ className, variant, size, ...props }: ButtonProps) {
    return <button className={cn(buttonVariants({ variant, size, className }))} {...props} />;
}

export { buttonVariants };
