import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';
import { FormEvent } from 'react';
import { ErrorSummary } from '@/Components/ErrorSummary';
import AppLayout from '@/Layouts/AppLayout';
import { FormField } from '@/Components/FormField';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Select } from '@/Components/ui/select';
import { Textarea } from '@/Components/ui/textarea';
import { UserRole } from '@/types';

interface UserFormData {
    name: string;
    email: string;
    password: string;
    phone: string;
    alamat: string;
    role: UserRole;
    is_active: boolean;
}

interface UserRecord extends Omit<UserFormData, 'password'> {
    id: number;
}

interface UsersFormProps {
    user?: UserRecord;
    roles: Record<UserRole, string>;
}

export default function UsersForm({ user, roles }: UsersFormProps) {
    const isEdit = Boolean(user);
    const { data, setData, post, put, processing, errors } = useForm<UserFormData>({
        name: user?.name ?? '',
        email: user?.email ?? '',
        password: '',
        phone: user?.phone ?? '',
        alamat: user?.alamat ?? '',
        role: user?.role ?? 'operator',
        is_active: user?.is_active ?? true,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();

        if (isEdit && data.is_active === false && !window.confirm('Nonaktifkan pengguna ini?')) {
            return;
        }

        if (user) {
            put(`/users/${user.id}`);
        } else {
            post('/users');
        }
    };

    return (
        <AppLayout title={isEdit ? 'Edit User' : 'Tambah User'}>
            <Head title={isEdit ? 'Edit User' : 'Tambah User'} />
            <div className="mb-4">
                <Link href="/users">
                    <Button variant="outline" size="sm">
                        <ArrowLeft size={16} />
                        Kembali
                    </Button>
                </Link>
            </div>
            <Card className="max-w-3xl">
                <CardHeader>
                    <CardTitle>{isEdit ? 'Edit User' : 'Tambah User'}</CardTitle>
                </CardHeader>
                <CardContent>
                    <form className="grid gap-4" onSubmit={submit}>
                        <ErrorSummary errors={errors} />
                        <div className="grid gap-4 md:grid-cols-2">
                            <FormField label="Nama" error={errors.name}>
                                <Input value={data.name} onChange={(event) => setData('name', event.target.value)} />
                            </FormField>
                            <FormField label="Email" error={errors.email}>
                                <Input type="email" value={data.email} onChange={(event) => setData('email', event.target.value)} />
                            </FormField>
                        </div>
                        <div className="grid gap-4 md:grid-cols-2">
                            <FormField label={isEdit ? 'Password baru' : 'Password'} error={errors.password}>
                                <Input
                                    type="password"
                                    value={data.password}
                                    onChange={(event) => setData('password', event.target.value)}
                                    placeholder={isEdit ? 'Kosongkan jika tidak diubah' : ''}
                                />
                            </FormField>
                            <FormField label="No. HP" error={errors.phone}>
                                <Input
                                    value={data.phone}
                                    inputMode="tel"
                                    onChange={(event) => setData('phone', event.target.value.replace(/\D/g, '').slice(0, 15))}
                                />
                            </FormField>
                        </div>
                        <div className="grid gap-4 md:grid-cols-2">
                            <FormField label="Role" error={errors.role}>
                                <Select value={data.role} onChange={(event) => setData('role', event.target.value as UserRole)}>
                                    {Object.entries(roles).map(([value, label]) => (
                                        <option key={value} value={value}>
                                            {label}
                                        </option>
                                    ))}
                                </Select>
                            </FormField>
                            <FormField label="Status" error={errors.is_active} hint={isEdit ? 'Akun nonaktif tidak bisa login.' : undefined}>
                                <Select
                                    value={data.is_active ? '1' : '0'}
                                    onChange={(event) => setData('is_active', event.target.value === '1')}
                                >
                                    <option value="1">Aktif</option>
                                    <option value="0">Nonaktif</option>
                                </Select>
                            </FormField>
                        </div>
                        <FormField label="Alamat" error={errors.alamat}>
                            <Textarea value={data.alamat} onChange={(event) => setData('alamat', event.target.value)} />
                        </FormField>
                        <div className="flex justify-end">
                            <Button type="submit" disabled={processing}>
                                <Save size={17} />
                                Simpan
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </AppLayout>
    );
}
