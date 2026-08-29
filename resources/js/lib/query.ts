import { router } from '@inertiajs/react';
import { FormEvent, useCallback, useEffect, useRef, useState } from 'react';

export type QueryValue = string | number | boolean | null | undefined;
export type QueryValues = Record<string, QueryValue>;

export function cleanQuery<T extends QueryValues>(query: T): QueryValues {
    return Object.fromEntries(
        Object.entries(query).filter(([, value]) => value !== '' && value !== null && value !== undefined),
    );
}

interface UseIndexFiltersOptions<T extends QueryValues> {
    url: string;
    values: T;
    debounceKeys?: Array<keyof T>;
    debounceMs?: number;
}

export function useIndexFilters<T extends QueryValues>({
    url,
    values,
    debounceKeys = [],
    debounceMs = 400,
}: UseIndexFiltersOptions<T>) {
    const [loading, setLoading] = useState(false);
    const mounted = useRef(false);
    const valuesRef = useRef<T>(values);
    const debouncedValue = debounceKeys.map((key) => String(values[key] ?? '')).join('\u001f');

    useEffect(() => {
        valuesRef.current = values;
    }, [values]);

    const visit = useCallback((event?: FormEvent, extra: QueryValues = {}) => {
        event?.preventDefault();
        setLoading(true);
        router.get(url, cleanQuery({ ...valuesRef.current, ...extra }), {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onFinish: () => setLoading(false),
        });
    }, [url]);

    const reset = useCallback(() => {
        setLoading(true);
        router.get(url, {}, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onFinish: () => setLoading(false),
        });
    }, [url]);

    useEffect(() => {
        if (!debounceKeys.length) {
            return;
        }

        if (!mounted.current) {
            mounted.current = true;
            return;
        }

        const timeout = window.setTimeout(() => visit(), debounceMs);

        return () => window.clearTimeout(timeout);
    }, [debounceKeys.length, debounceMs, debouncedValue, visit]);

    return { loading, reset, visit };
}
