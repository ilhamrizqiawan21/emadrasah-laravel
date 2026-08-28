import * as React from 'react';
import { cn } from '@/lib/utils';

interface TabsProps {
    tabs: Array<{ value: string; label: string }>;
    value: string;
    onValueChange: (value: string) => void;
}

export function Tabs({ tabs, value, onValueChange }: TabsProps) {
    return (
        <div className="inline-flex max-w-full gap-1 overflow-x-auto rounded-md bg-slate-100 p-1" role="tablist">
            {tabs.map((tab) => (
                <button
                    key={tab.value}
                    type="button"
                    role="tab"
                    aria-selected={value === tab.value}
                    className={cn(
                        'h-9 shrink-0 rounded px-3 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-blue-500',
                        value === tab.value ? 'bg-white text-slate-950 shadow-sm' : 'text-slate-600 hover:text-slate-950',
                    )}
                    onClick={() => onValueChange(tab.value)}
                >
                    {tab.label}
                </button>
            ))}
        </div>
    );
}
