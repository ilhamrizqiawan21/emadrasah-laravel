import { Link } from '@inertiajs/react';
import { PaginationLink } from '@/types';
import { cn } from '@/lib/utils';

interface PaginationProps {
    links: PaginationLink[];
}

export function Pagination({ links }: PaginationProps) {
    if (links.length <= 3) {
        return null;
    }

    return (
        <nav className="flex flex-wrap items-center gap-2" aria-label="Navigasi halaman">
            {links.map((link, index) => {
                const label = link.label.replace('&laquo;', '').replace('&raquo;', '');
                const classes = cn(
                    'inline-flex h-9 min-w-9 items-center justify-center rounded-md border px-3 text-sm font-semibold',
                    link.active
                        ? 'border-blue-600 bg-blue-600 text-white'
                        : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50',
                    !link.url && 'cursor-not-allowed opacity-50',
                );

                return link.url ? (
                    <Link
                        key={`${label}-${index}`}
                        href={link.url}
                        className={classes}
                        preserveScroll
                        preserveState
                        aria-current={link.active ? 'page' : undefined}
                        aria-label={link.active ? `Halaman ${label}, sedang aktif` : `Buka halaman ${label}`}
                    >
                        {label}
                    </Link>
                ) : (
                    <span key={`${label}-${index}`} className={classes} aria-disabled="true">
                        {label}
                    </span>
                );
            })}
        </nav>
    );
}
