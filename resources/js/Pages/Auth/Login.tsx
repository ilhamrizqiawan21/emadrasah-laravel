import { Head, useForm } from '@inertiajs/react';
import { BookOpen, LogIn } from 'lucide-react';
import { FormEvent } from 'react';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { FormField } from '@/Components/FormField';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        post('/login');
    };

    return (
        <>
            <Head title="Login" />
            <main className="flex min-h-screen items-center justify-center bg-slate-100 p-4">
                <Card className="w-full max-w-md">
                    <CardHeader>
                        <div className="mb-5 flex items-center gap-3">
                            <div className="flex h-11 w-11 items-center justify-center rounded-md bg-blue-600 text-white">
                                <BookOpen size={22} />
                            </div>
                            <div>
                                <div className="font-display text-xl font-extrabold text-slate-950">eMadrasah</div>
                                <div className="text-sm font-medium text-slate-500">MTs Al-Ihsan Batujajar</div>
                            </div>
                        </div>
                        <CardTitle>Masuk ke aplikasi</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form className="grid gap-4" onSubmit={submit}>
                            <FormField label="Email" error={errors.email}>
                                <Input
                                    type="email"
                                    value={data.email}
                                    autoComplete="username"
                                    onChange={(event) => setData('email', event.target.value)}
                                />
                            </FormField>
                            <FormField label="Password" error={errors.password}>
                                <Input
                                    type="password"
                                    value={data.password}
                                    autoComplete="current-password"
                                    onChange={(event) => setData('password', event.target.value)}
                                />
                            </FormField>
                            <label className="flex items-center gap-2 text-sm font-semibold text-slate-700">
                                <input
                                    type="checkbox"
                                    checked={data.remember}
                                    onChange={(event) => setData('remember', event.target.checked)}
                                />
                                Ingat saya
                            </label>
                            <Button type="submit" disabled={processing}>
                                <LogIn size={17} />
                                Login
                            </Button>
                        </form>
                    </CardContent>
                </Card>
            </main>
        </>
    );
}
