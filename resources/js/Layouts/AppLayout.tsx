import { Link, router, usePage } from '@inertiajs/react';
import {
    Archive,
    BookOpen,
    Boxes,
    CalendarDays,
    ClipboardList,
    FileText,
    GraduationCap,
    Home,
    LogOut,
    Menu,
    School,
    Settings,
    Users,
    X,
} from 'lucide-react';
import { ReactNode, useState } from 'react';
import { Button } from '@/Components/ui/button';
import { PageProps } from '@/types';
import { cn } from '@/lib/utils';

const navGroups = [
    {
        label: 'Dashboard',
        items: [{ label: 'Dashboard', href: '/dashboard', icon: Home, active: ['/dashboard'], permission: 'dashboard.view' }],
    },
    {
        label: 'Master Data',
        items: [
            { label: 'Guru', href: '/guru', icon: GraduationCap, active: ['/guru'], permission: 'guru.view' },
            { label: 'Kelas', href: '/kelas', icon: School, active: ['/kelas'], permission: 'kelas.view' },
            { label: 'Mata Pelajaran', href: '/mapel', icon: BookOpen, active: ['/mapel'], permission: 'mapel.view' },
            { label: 'Jam Pelajaran', href: '/jam-pelajaran', icon: CalendarDays, active: ['/jam-pelajaran'], permission: 'jam_pelajaran.view' },
            { label: 'Tahun Pelajaran', href: '/tahun-pelajaran', icon: CalendarDays, active: ['/tahun-pelajaran'], permission: 'tahun_pelajaran.view' },
        ],
    },
    {
        label: 'Akademik',
        items: [
            { label: 'Siswa', href: '/siswa', icon: Users, active: ['/siswa'], permission: 'siswa.view' },
            { label: 'Buku Induk', href: '/buku-induk', icon: BookOpen, active: ['/buku-induk'], permission: 'buku_induk.view' },
            { label: 'Raport', href: '/raport', icon: FileText, active: ['/raport'], permission: 'raport.view' },
            { label: 'Jadwal', href: '/jadwal', icon: CalendarDays, active: ['/jadwal'], permission: 'jadwal.view' },
            { label: 'Absensi', href: '/absensi', icon: ClipboardList, active: ['/absensi'], permission: 'absensi.view' },
            { label: 'Arsip Akademik', href: '/arsip-akademik', icon: Archive, active: ['/arsip-akademik'], permission: 'arsip_akademik.view' },
        ],
    },
    {
        label: 'Administrasi',
        items: [
            { label: 'Surat Masuk', href: '/surat-masuk', icon: FileText, active: ['/surat-masuk'], permission: 'surat_masuk.view' },
            { label: 'Surat Keluar', href: '/surat-keluar', icon: FileText, active: ['/surat-keluar'], permission: 'surat_keluar.view' },
            { label: 'Template Surat', href: '/template-surat', icon: FileText, active: ['/template-surat'], permission: 'template_surat.view' },
            { label: 'Tugas TU', href: '/tasks', icon: ClipboardList, active: ['/tasks'], permission: 'tasks.view' },
        ],
    },
    {
        label: 'Sarpras',
        items: [
            { label: 'Sarana', href: '/sarana', icon: Boxes, active: ['/sarana'], permission: 'sarana.view' },
            { label: 'Kategori Sarana', href: '/kategori-sarana', icon: Boxes, active: ['/kategori-sarana'], permission: 'kategori_sarana.view' },
        ],
    },
    {
        label: 'Sistem',
        items: [{ label: 'Pengguna', href: '/users', icon: Settings, active: ['/users'], permission: 'users.view' }],
    },
];

const sectionLabels = navGroups.flatMap((group) => group.items.map((item) => ({ group: group.label, ...item })));
const inertiaRoutes = [
    '/dashboard',
    '/users',
    '/guru',
    '/kelas',
    '/mapel',
    '/jam-pelajaran',
    '/tahun-pelajaran',
    '/jadwal',
    '/absensi',
    '/surat-masuk',
    '/surat-keluar',
    '/template-surat',
    '/tasks',
];

function isInertiaRoute(href: string) {
    return inertiaRoutes.some((route) => href === route || href.startsWith(`${route}/`));
}

interface AppLayoutProps {
    title: string;
    children: ReactNode;
}

export default function AppLayout({ title, children }: AppLayoutProps) {
    const { auth, flash } = usePage<PageProps>().props;
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const path = window.location.pathname;

    const logout = () => router.post('/logout');
    const can = (permission: string) => {
        const permissions = auth.user?.permissions ?? [];

        return permissions.some((allowed) => {
            if (allowed === '*' || allowed === permission) {
                return true;
            }

            return allowed.endsWith('.*') && permission.startsWith(allowed.slice(0, -1));
        });
    };
    const groups = navGroups
        .map((group) => ({
            ...group,
            items: group.items.filter((item) => can(item.permission)),
        }))
        .filter((group) => group.items.length > 0);
    const activeItem = sectionLabels.find((item) => item.active.some((prefix) => path === prefix || path.startsWith(`${prefix}/`)));

    return (
        <div className="min-h-screen bg-slate-100">
            <aside
                className={cn(
                    'fixed inset-y-0 left-0 z-40 w-72 border-r border-slate-200 bg-white transition-transform lg:translate-x-0',
                    sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                )}
            >
                <div className="flex h-16 items-center justify-between border-b border-slate-200 px-5">
                    <Link href="/dashboard" className="flex items-center gap-3">
                        <div className="flex h-10 w-10 items-center justify-center rounded-md bg-blue-600 text-white">
                            <BookOpen size={21} />
                        </div>
                        <div>
                            <div className="font-display text-base font-extrabold text-slate-950">eMadrasah</div>
                            <div className="text-xs font-semibold text-slate-500">MTs Al-Ihsan</div>
                        </div>
                    </Link>
                    <Button variant="ghost" size="icon" className="lg:hidden" onClick={() => setSidebarOpen(false)} aria-label="Tutup sidebar">
                        <X size={18} />
                    </Button>
                </div>
                <nav className="grid gap-5 p-3">
                    {groups.map((group) => (
                        <div key={group.label}>
                            <div className="px-3 pb-2 text-[11px] font-bold uppercase tracking-wide text-slate-400">{group.label}</div>
                            <div className="grid gap-1">
                                {group.items.map((item) => {
                                    const Icon = item.icon;
                                    const active = item.active.some((prefix) => path === prefix || path.startsWith(`${prefix}/`));
                                    const itemClassName = cn(
                                        'flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-semibold transition',
                                        active ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-100',
                                    );
                                    const content = (
                                        <>
                                            <Icon size={18} />
                                            {item.label}
                                        </>
                                    );

                                    return isInertiaRoute(item.href) ? (
                                        <Link
                                            key={item.label}
                                            href={item.href}
                                            aria-current={active ? 'page' : undefined}
                                            className={itemClassName}
                                        >
                                            {content}
                                        </Link>
                                    ) : (
                                        <a key={item.label} href={item.href} aria-current={active ? 'page' : undefined} className={itemClassName}>
                                            {content}
                                        </a>
                                    );
                                })}
                            </div>
                        </div>
                    ))}
                </nav>
            </aside>

            {sidebarOpen ? (
                <button
                    className="fixed inset-0 z-30 bg-slate-950/40 lg:hidden"
                    aria-label="Tutup sidebar"
                    onClick={() => setSidebarOpen(false)}
                />
            ) : null}

            <div className="lg:pl-72">
                <header className="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-200 bg-white/95 px-4 backdrop-blur lg:px-8">
                    <div className="flex items-center gap-3">
                        <Button variant="ghost" size="icon" className="lg:hidden" onClick={() => setSidebarOpen(true)} aria-label="Buka sidebar">
                            <Menu size={19} />
                        </Button>
                        <div>
                            {activeItem ? (
                                <div className="mb-0.5 flex items-center gap-1 text-xs font-semibold text-slate-500">
                                    <Link href="/dashboard" className="text-blue-700">
                                        Dashboard
                                    </Link>
                                    <span>/</span>
                                    <span>{activeItem.group}</span>
                                    <span>/</span>
                                    <span>{activeItem.label}</span>
                                </div>
                            ) : null}
                            <h1 className="font-display text-lg font-extrabold text-slate-950">{title}</h1>
                        </div>
                    </div>
                    <div className="flex items-center gap-3">
                        <div className="hidden text-right sm:block">
                            <div className="text-sm font-bold text-slate-900">{auth.user?.name}</div>
                            <div className="text-xs font-semibold uppercase text-slate-500">{auth.user?.role}</div>
                        </div>
                        <Button variant="outline" size="sm" onClick={logout}>
                            <LogOut size={16} />
                            Logout
                        </Button>
                    </div>
                </header>

                <main className="p-4 lg:p-8">
                    {flash.success ? (
                        <div className="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                            {flash.success}
                        </div>
                    ) : null}
                    {flash.error ? (
                        <div className="mb-4 rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800">
                            {flash.error}
                        </div>
                    ) : null}
                    {children}
                </main>
            </div>
        </div>
    );
}
