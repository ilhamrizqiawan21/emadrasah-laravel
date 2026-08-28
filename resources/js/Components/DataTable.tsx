import { ArrowUpDown } from 'lucide-react';
import { ReactNode } from 'react';
import { Table, TableContainer, TBody, Td, Th, THead, Tr } from '@/Components/ui/table';
import { cn } from '@/lib/utils';

export interface DataTableColumn<T> {
    key: string;
    label: string;
    sortable?: boolean;
    className?: string;
    headerClassName?: string;
    render: (row: T, index: number) => ReactNode;
}

interface DataTableProps<T> {
    data: T[];
    columns: Array<DataTableColumn<T>>;
    loading?: boolean;
    emptyTitle?: string;
    emptyDescription?: string;
    sort?: string;
    direction?: string;
    onSort?: (column: string) => void;
}

export function DataTable<T>({
    data,
    columns,
    loading = false,
    emptyTitle = 'Data belum tersedia',
    emptyDescription = 'Data yang cocok dengan filter akan tampil di sini.',
    sort,
    direction,
    onSort,
}: DataTableProps<T>) {
    return (
        <TableContainer>
            {loading ? (
                <div className="absolute inset-0 z-10 grid place-items-center bg-white/70 text-sm font-semibold text-slate-600">
                    Memuat data...
                </div>
            ) : null}
            <Table>
                <THead>
                    <Tr>
                        {columns.map((column) => {
                            const active = sort === column.key;

                            return (
                                <Th key={column.key} className={column.headerClassName}>
                                    {column.sortable && onSort ? (
                                        <button
                                            type="button"
                                            className={cn(
                                                'inline-flex items-center gap-1 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2',
                                                column.className,
                                            )}
                                            onClick={() => onSort(column.key)}
                                            aria-label={`Urutkan berdasarkan ${column.label}`}
                                            aria-sort={active ? (direction === 'asc' ? 'ascending' : 'descending') : undefined}
                                        >
                                            {column.label}
                                            <ArrowUpDown size={13} className={active ? 'text-blue-600' : 'text-slate-400'} />
                                        </button>
                                    ) : (
                                        column.label
                                    )}
                                </Th>
                            );
                        })}
                    </Tr>
                </THead>
                <TBody>
                    {data.length ? (
                        data.map((row, index) => (
                            <Tr key={index}>
                                {columns.map((column) => (
                                    <Td key={column.key} className={column.className}>
                                        {column.render(row, index)}
                                    </Td>
                                ))}
                            </Tr>
                        ))
                    ) : (
                        <Tr>
                            <Td colSpan={columns.length} className="py-10 text-center">
                                <div className="font-semibold text-slate-700">{emptyTitle}</div>
                                <div className="mt-1 text-sm font-medium text-slate-500">{emptyDescription}</div>
                            </Td>
                        </Tr>
                    )}
                </TBody>
            </Table>
        </TableContainer>
    );
}
