interface ErrorSummaryProps {
    errors: Record<string, string>;
}

export function ErrorSummary({ errors }: ErrorSummaryProps) {
    const messages = Object.values(errors).filter(Boolean);

    if (messages.length === 0) {
        return null;
    }

    return (
        <div className="rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <div className="font-bold">Periksa kembali isian form.</div>
            <ul className="mt-2 list-disc space-y-1 pl-5">
                {messages.map((message) => (
                    <li key={message}>{message}</li>
                ))}
            </ul>
        </div>
    );
}
