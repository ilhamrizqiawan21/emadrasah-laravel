import { ReactNode } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';

interface ResourceFormProps {
    title: string;
    children: ReactNode;
    actions: ReactNode;
    onSubmit: (event: React.FormEvent) => void;
}

export function ResourceForm({ title, children, actions, onSubmit }: ResourceFormProps) {
    return (
        <Card className="max-w-4xl">
            <CardHeader>
                <CardTitle>{title}</CardTitle>
            </CardHeader>
            <CardContent>
                <form className="grid gap-4" onSubmit={onSubmit}>
                    {children}
                    <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">{actions}</div>
                </form>
            </CardContent>
        </Card>
    );
}
