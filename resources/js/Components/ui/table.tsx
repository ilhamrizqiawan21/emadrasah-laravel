import * as React from 'react';
import { cn } from '@/lib/utils';

export function TableContainer({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) {
    return <div className={cn('relative w-full overflow-x-auto rounded-md border border-slate-200', className)} {...props} />;
}

export function Table({ className, ...props }: React.TableHTMLAttributes<HTMLTableElement>) {
    return <table className={cn('w-full min-w-[44rem] text-left text-sm', className)} {...props} />;
}

export function THead({ className, ...props }: React.HTMLAttributes<HTMLTableSectionElement>) {
    return <thead className={cn('bg-slate-50 text-xs uppercase tracking-wide text-slate-500', className)} {...props} />;
}

export function TBody({ className, ...props }: React.HTMLAttributes<HTMLTableSectionElement>) {
    return <tbody className={cn('divide-y divide-slate-200', className)} {...props} />;
}

export function Tr({ className, ...props }: React.HTMLAttributes<HTMLTableRowElement>) {
    return <tr className={cn('border-b border-slate-200 last:border-0', className)} {...props} />;
}

export function Th({ className, ...props }: React.ThHTMLAttributes<HTMLTableCellElement>) {
    return <th className={cn('px-4 py-3 font-semibold', className)} {...props} />;
}

export function Td({ className, ...props }: React.TdHTMLAttributes<HTMLTableCellElement>) {
    return <td className={cn('px-4 py-3 text-slate-700', className)} {...props} />;
}
