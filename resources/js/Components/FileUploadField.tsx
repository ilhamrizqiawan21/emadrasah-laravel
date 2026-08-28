import { FormField } from '@/Components/FormField';
import { Input } from '@/Components/ui/input';

interface FileUploadFieldProps {
    label: string;
    name: string;
    error?: string;
    accept?: string;
    hint?: string;
    onChange: (file: File | null) => void;
}

export function FileUploadField({ label, name, error, accept, hint, onChange }: FileUploadFieldProps) {
    return (
        <FormField label={label} error={error} hint={hint}>
            <Input
                type="file"
                name={name}
                accept={accept}
                onChange={(event) => onChange(event.target.files?.[0] ?? null)}
            />
        </FormField>
    );
}
