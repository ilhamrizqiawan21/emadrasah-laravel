import { expect, test } from '@playwright/test';
import { pngBuffer } from './support.js';

const SUBMIT = '.em-main button[type="submit"]';

async function login(page, email = 'admin@madrasah.id', password = 'admin123') {
    await page.goto('/login');
    await page.fill('#email', email);
    await page.fill('#password', password);
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard');
}

async function logout(page) {
    await page.evaluate(() => document.querySelector('form[action$="/logout"]').submit());
    await page.waitForURL('**/login');
}

test.describe('masuk dan keluar', () => {
    // Semua tes di sini dimulai sebagai tamu (tanpa sesi admin bersama).
    test.use({ storageState: { cookies: [], origins: [] } });

    test('kata sandi salah menampilkan galat dan tetap di halaman login', async ({ page }) => {
        await page.goto('/login');
        await page.fill('#email', 'tidak-ada@madrasah.id');
        await page.fill('#password', 'salah-total');
        await page.click('button[type="submit"]');

        await expect(page.locator('.em-alert-content')).toContainText('Email atau password salah');
        await expect(page).toHaveURL(/\/login$/);
    });

    test('tamu yang membuka halaman terlindungi diarahkan ke login', async ({ page }) => {
        await page.goto('/siswa');
        await expect(page).toHaveURL(/\/login$/);
    });

    test('admin masuk, melihat dashboard, lalu keluar', async ({ page }) => {
        await login(page);
        await expect(page.locator('.dash-greeting__label')).toContainText(/Selamat (pagi|siang|sore|malam)/);

        await logout(page);
        await page.goto('/dashboard');
        await expect(page).toHaveURL(/\/login$/);
    });

    test('guru tidak bisa membuka halaman manajemen', async ({ page }) => {
        await login(page, 'guru@madrasah.id', 'guru123');
        const response = await page.goto('/siswa');
        expect(response.status()).toBe(403);
    });
});

test.describe('data siswa', () => {
    test('menambah, mengubah, mencari, dan menghapus siswa lewat antarmuka', async ({ page }) => {

        // Tambah
        await page.goto('/siswa/create');
        await page.fill('[name="nis"]', '8800123');
        await page.fill('[name="nama_lengkap"]', 'Siswa Uji E2E');
        await page.selectOption('[name="kelas_id"]', { index: 1 });
        await page.selectOption('[name="jenis_kelamin"]', 'L');
        await page.selectOption('[name="status"]', 'Aktif');
        await page.click(SUBMIT);
        await page.waitForURL('**/siswa');
        await expect(page.locator('.em-alert-content')).toContainText('berhasil ditambahkan');
        await expect(page.locator('tbody')).toContainText('Siswa Uji E2E');

        // Cari
        await page.fill('[name="search"]', 'Siswa Uji E2E');
        await page.click('button:has-text("Filter")');
        await expect(page.locator('tbody tr')).toHaveCount(1);

        // Ubah
        await page.click('a[title="Edit"]');
        await page.fill('[name="nama_lengkap"]', 'Siswa Uji Diubah');
        await page.click(SUBMIT);
        await page.waitForURL('**/siswa**');
        await page.fill('[name="search"]', 'Diubah');
        await page.click('button:has-text("Filter")');
        await expect(page.locator('tbody')).toContainText('Siswa Uji Diubah');

        // Hapus lewat modal konfirmasi
        await page.click('button[title="Hapus"]');
        await expect(page.locator('#emConfirmModalBs')).toBeVisible();
        await page.click('#emConfirmOk');
        await page.waitForLoadState('networkidle');
        await page.goto('/siswa?search=Diubah');
        await expect(page.locator('tbody')).toContainText('Data siswa tidak ditemukan');
    });

    test('form menolak isian yang tidak lengkap tanpa menyimpan', async ({ page }) => {
        await page.goto('/siswa/create');
        await page.fill('[name="nama_lengkap"]', 'Tanpa NIS');
        await page.click(SUBMIT);

        // Tetap di form (validasi klien atau server) dan tidak ada siswa baru.
        await expect(page).toHaveURL(/\/siswa\/create/);
        await page.goto('/siswa?search=Tanpa NIS');
        await expect(page.locator('tbody')).toContainText('Data siswa tidak ditemukan');
    });
});

test.describe('pengaturan madrasah', () => {
    test('mengganti nama, warna, dan logo langsung terlihat di seluruh aplikasi', async ({ page, browser }) => {
        await page.goto('/pengaturan');

        await page.fill('#nama', 'Madrasah Ganti E2E');
        await page.fill('#nama_pendek', 'MG E2E');
        await page.click('.em-swatch[data-color="#1d4ed8"]');
        await page.setInputFiles('#logo', { name: 'logo.png', mimeType: 'image/png', buffer: pngBuffer(96) });
        await page.click('button:has-text("Simpan Pengaturan")');
        await page.waitForURL('**/pengaturan');
        await expect(page.locator('.em-alert-content')).toContainText('Pengaturan berhasil disimpan');

        // Sidebar: nama singkat, logo baru, dan warna tema
        await expect(page.locator('.em-brand__sub')).toHaveText('MG E2E');
        const logoWidth = await page.locator('.em-brand__logo img').evaluate((img) => img.naturalWidth);
        expect(logoWidth).toBe(96);
        const primary = await page.evaluate(() => getComputedStyle(document.documentElement).getPropertyValue('--em-green-700').trim());
        expect(primary).toBe('#1d4ed8');

        // Sebagai tamu (konteks terpisah, tanpa keluar dari sesi admin): halaman login ikut berubah
        const guest = await browser.newContext({ storageState: { cookies: [], origins: [] } });
        const guestPage = await guest.newPage();
        await guestPage.goto('/login');
        await expect(guestPage.locator('.login-brand__name')).toHaveText('Madrasah Ganti E2E');
        await expect(guestPage.locator('.login-logo__img')).toBeVisible();
        await guest.close();

        // Kembalikan agar tes lain tidak terpengaruh
        await page.goto('/pengaturan');
        await page.fill('#nama', 'Madrasah Uji E2E');
        await page.fill('#nama_pendek', 'MU E2E');
        await page.click('.em-swatch[data-color="#047857"]');
        await page.check('#hapus_logo');
        await page.click('button:has-text("Simpan Pengaturan")');
        await page.waitForURL('**/pengaturan');
        await expect(page.locator('.em-brand__sub')).toHaveText('MU E2E');
    });

    test('warna yang terlalu terang ditolak dan tidak tersimpan', async ({ page }) => {
        await page.goto('/pengaturan');
        await page.fill('#warna_utama', '#ffff00');
        await page.click('button:has-text("Simpan Pengaturan")');

        await expect(page.locator('.alert-danger')).toContainText('belum tersimpan');
        await expect(page.locator('body')).toContainText('terlalu terang');
    });
});

test.describe('tampilan HP', () => {
    test.use({ viewport: { width: 375, height: 800 } });

    test('tabel daftar menjadi kartu berlabel tanpa overflow horizontal', async ({ page }) => {
        await page.goto('/siswa');

        await expect(page.locator('table thead')).toBeHidden();
        await expect(page.locator('td[data-label="Kelas"]').first()).toBeVisible();
        await expect(page.locator('td[data-label="Status"]').first()).toBeVisible();

        const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
        expect(overflow).toBeLessThanOrEqual(0);
    });

    test('menu samping terbuka lewat tombol hamburger', async ({ page }) => {
        await page.goto('/dashboard');
        await page.click('#sidebarToggleMobile');
        await expect(page.locator('#emSidebar')).toHaveClass(/is-open/);
    });

    test('absensi guru tampil kompak', async ({ page }) => {
        await page.goto('/absensi');
        const height = await page.locator('.absensi-row').first().evaluate((row) => row.getBoundingClientRect().height);
        expect(height).toBeLessThan(130);
    });
});

test.describe('keamanan unggahan (server sungguhan)', () => {
    test('berkas bernama .pdf yang isinya PHP ditolak, PDF asli diterima', async ({ page }) => {

        // Yang tidak bisa dibuktikan oleh UploadedFile::fake(): server memeriksa ISI berkas, bukan ekstensinya.
        await page.goto('/surat-masuk/create');
        await page.fill('[name="asal_surat"]', 'Penyerang');
        await page.fill('[name="perihal"]', 'Unggahan palsu E2E');
        await page.fill('[name="tanggal_terima"]', '2026-10-01');
        await page.setInputFiles('[name="file_scan"]', { name: 'palsu.pdf', mimeType: 'application/pdf', buffer: Buffer.from('<?php echo shell_exec($_GET["c"]); ?>') });
        await page.click(SUBMIT);

        await expect(page).toHaveURL(/\/surat-masuk\/create/);
        await expect(page.locator('body')).toContainText(/file scan/i);
        await page.goto('/surat-masuk');
        await expect(page.locator('body')).not.toContainText('Unggahan palsu E2E');

        // PDF asli diterima dan hanya bisa dibuka setelah login
        await page.goto('/surat-masuk/create');
        await page.fill('[name="asal_surat"]', 'Dinas');
        await page.fill('[name="perihal"]', 'Unggahan asli E2E');
        await page.fill('[name="tanggal_terima"]', '2026-10-01');
        await page.setInputFiles('[name="file_scan"]', { name: 'asli.pdf', mimeType: 'application/pdf', buffer: Buffer.from('%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF\n') });
        await page.click(SUBMIT);
        await page.waitForURL('**/surat-masuk');
        await expect(page.locator('tbody')).toContainText('Unggahan asli E2E');
    });
});

test.describe('sidebar: tombol buka-tutup', () => {
    const geometry = (page) => page.evaluate(() => {
        const box = (selector) => document.querySelector(selector).getBoundingClientRect();
        const sidebar = box('#emSidebar');
        const toggle = box('#sidebarToggleDesktop');
        const logo = box('.em-brand__logo');
        const main = box('.em-main');

        return {
            left: sidebar.left, top: sidebar.top, height: sidebar.height, right: sidebar.right, width: sidebar.width,
            toggleX: toggle.left + toggle.width / 2, toggleY: toggle.top + toggle.height / 2,
            logoY: logo.top + logo.height / 2, mainLeft: main.left, viewport: window.innerHeight,
        };
    });

    test('presisi: sidebar menempel penuh, tombol tepat di tepi dan sejajar logo', async ({ page }) => {
        await page.goto('/dashboard');
        await page.evaluate(() => localStorage.removeItem('em_sidebar_collapsed'));
        await page.reload();

        const g = await geometry(page);
        expect(g.left).toBe(0);
        expect(g.top).toBe(0);
        expect(g.height).toBe(g.viewport);
        expect(Math.abs(g.toggleX - g.right)).toBeLessThanOrEqual(0.5); // titik tengah tepat di tepi sidebar
        expect(Math.abs(g.toggleY - g.logoY)).toBeLessThanOrEqual(0.5); // sejajar dengan logo
        expect(g.mainLeft).toBeGreaterThanOrEqual(g.right);              // konten tidak tertimpa sidebar
    });

    test('menciutkan dan melebarkan: lebar, konten, dan status ARIA ikut berubah', async ({ page }) => {
        await page.goto('/dashboard');
        await page.evaluate(() => localStorage.removeItem('em_sidebar_collapsed'));
        await page.reload();
        const toggle = page.locator('#sidebarToggleDesktop');

        await expect(toggle).toHaveAttribute('aria-expanded', 'true');
        const open = await geometry(page);

        await toggle.click();
        await expect(page.locator('#emSidebar')).toHaveClass(/is-collapsed/);
        await expect(toggle).toHaveAttribute('aria-expanded', 'false');
        await expect(toggle).toHaveAttribute('aria-label', 'Lebarkan menu');
        await page.waitForTimeout(450); // tunggu transisi
        const closed = await geometry(page);
        expect(closed.width).toBeLessThan(open.width);
        expect(closed.mainLeft).toBeLessThan(open.mainLeft);
        expect(closed.mainLeft).toBeGreaterThanOrEqual(closed.right);
        expect(Math.abs(closed.toggleX - closed.right)).toBeLessThanOrEqual(0.5); // tetap tepat di tepi saat tertutup
        expect(Math.abs(closed.toggleY - closed.logoY)).toBeLessThanOrEqual(0.5);

        await toggle.click();
        await expect(page.locator('#emSidebar')).not.toHaveClass(/is-collapsed/);
        await expect(toggle).toHaveAttribute('aria-expanded', 'true');
        await expect(toggle).toHaveAttribute('aria-label', 'Ciutkan menu');
    });

    test('pilihan ciut bertahan setelah muat ulang dan pindah halaman', async ({ page }) => {
        await page.goto('/dashboard');
        await page.evaluate(() => localStorage.removeItem('em_sidebar_collapsed'));
        await page.reload();

        await page.click('#sidebarToggleDesktop');
        await page.reload();
        await expect(page.locator('#emSidebar')).toHaveClass(/is-collapsed/);

        await page.goto('/siswa');
        await expect(page.locator('#emSidebar')).toHaveClass(/is-collapsed/);
        await expect(page.locator('#sidebarToggleDesktop')).toHaveAttribute('aria-expanded', 'false');

        await page.click('#sidebarToggleDesktop');
        await page.goto('/guru');
        await expect(page.locator('#emSidebar')).not.toHaveClass(/is-collapsed/);
    });

    test('keyboard: Ctrl+B dan Enter pada tombol, tetapi Ctrl+B diabaikan saat mengetik', async ({ page }) => {
        await page.goto('/siswa');
        await page.evaluate(() => localStorage.removeItem('em_sidebar_collapsed'));
        await page.reload();
        const sidebar = page.locator('#emSidebar');

        await page.keyboard.press('Control+b');
        await expect(sidebar).toHaveClass(/is-collapsed/);
        await page.keyboard.press('Control+b');
        await expect(sidebar).not.toHaveClass(/is-collapsed/);

        // Saat fokus di kolom isian, Ctrl+B adalah milik pengetikan (tebal), bukan sidebar.
        await page.focus('[name="search"]');
        await page.keyboard.press('Control+b');
        await expect(sidebar).not.toHaveClass(/is-collapsed/);

        // Tombol dapat dioperasikan dengan keyboard
        await page.focus('#sidebarToggleDesktop');
        await page.keyboard.press('Enter');
        await expect(sidebar).toHaveClass(/is-collapsed/);
    });

    test.describe('di HP', () => {
        test.use({ viewport: { width: 375, height: 800 } });

        test('laci menu: dibuka hamburger, ditutup tombol X, overlay, dan Escape', async ({ page }) => {
            await page.goto('/dashboard');
            const sidebar = page.locator('#emSidebar');
            await expect(page.locator('#sidebarToggleDesktop')).toBeHidden();

            const open = async () => { await page.click('#sidebarToggleMobile'); await expect(sidebar).toHaveClass(/is-open/); };

            await open();
            await expect(page.locator('#sidebarClose')).toBeVisible();
            await page.click('#sidebarClose');
            await expect(sidebar).not.toHaveClass(/is-open/);

            await open();
            await page.mouse.click(350, 400); // di luar laci (overlay)
            await expect(sidebar).not.toHaveClass(/is-open/);

            await open();
            await page.keyboard.press('Escape');
            await expect(sidebar).not.toHaveClass(/is-open/);
        });
    });
});

test.describe('surat keluar: pencarian siswa', () => {
    test('mengetik nama menampilkan hasil terbatas dan memilihnya mengisi kolom Tujuan', async ({ page }) => {
        await page.goto('/surat-keluar/create');
        const input = page.locator('#cariSiswa');
        const results = page.locator('#hasilSiswa');

        // Satu huruf belum mencari apa pun
        await input.fill('A');
        await page.waitForTimeout(450);
        await expect(results).toBeHidden();

        await input.fill('Aisyah');
        await expect(results.locator('button')).toHaveCount(1);
        await expect(results.locator('button').first()).toContainText('Aisyah Putri Azzahra (NIS: 2425002)');

        await results.locator('button').first().click();
        await expect(page.locator('[name="tujuan"]')).toHaveValue('Aisyah Putri Azzahra (NIS: 2425002)');
        await expect(results).toBeHidden();
        await expect(input).toHaveValue('');
    });

    test('tidak ada hasil menampilkan pesan, bukan daftar kosong', async ({ page }) => {
        await page.goto('/surat-keluar/create');
        await page.fill('#cariSiswa', 'zzzzzzzz');
        await expect(page.locator('#hasilSiswa')).toContainText('Siswa tidak ditemukan');
    });

    test('halaman tidak memuat daftar siswa di dalam HTML', async ({ page }) => {
        const response = await page.goto('/surat-keluar/create');
        const html = await response.text();
        expect(html).not.toContain('Aisyah Putri Azzahra');
    });
});

test.describe('absensi siswa harian', () => {
    test('admin menandai siswa sakit, simpan, lalu data muncul lagi dan masuk rekap', async ({ page }) => {
        await page.goto('/absensi-siswa');
        const baris = page.locator('.em-main tbody tr').first();
        await expect(baris.locator('select')).toHaveValue('hadir');

        await baris.locator('select').selectOption('sakit');
        await baris.locator('input[type="text"]').fill('Demam');
        await page.click('#formAbsensiSiswa button[type="submit"]');

        await expect(page.locator('.em-alert-content')).toContainText('Absensi siswa berhasil disimpan');
        const setelah = page.locator('.em-main tbody tr').first();
        await expect(setelah.locator('select')).toHaveValue('sakit');
        await expect(setelah.locator('input[type="text"]')).toHaveValue('Demam');

        await page.goto('/absensi-siswa/rekap');
        await expect(page.locator('.em-main tbody tr').first().locator('td').nth(4)).toHaveText('1');
    });
});

test.describe('input nilai per kelas', () => {
    test('admin mengisi nilai satu siswa, simpan, lalu nilai tampil lagi', async ({ page }) => {
        await page.goto('/nilai');
        const baris = page.locator('.em-main tbody tr').first();
        await baris.locator('input[type="number"]').fill('87');
        await baris.locator('input[type="text"]').fill('Paham materi');
        await page.click('.em-main form[method="POST"][action$="/nilai"] button[type="submit"]');

        await expect(page.locator('.em-alert-content')).toContainText('Nilai berhasil disimpan');
        const setelah = page.locator('.em-main tbody tr').first();
        await expect(setelah.locator('input[type="number"]')).toHaveValue('87');
        await expect(setelah.locator('input[type="text"]')).toHaveValue('Paham materi');
    });
});

test.describe('portal guru', () => {
    test.use({ storageState: { cookies: [], origins: [] } });

    test('guru melihat kelas dan jadwalnya, lalu membuka daftar siswa kelasnya', async ({ page }) => {
        await login(page, 'guru@madrasah.id', 'guru123');
        await page.goto('/portal');

        await expect(page.locator('.em-page-title')).toHaveText('Kelas & Jadwal Saya');
        await page.getByRole('link', { name: 'Daftar siswa' }).first().click();
        await expect(page).toHaveURL(/\/portal\/kelas\/\d+$/);
        await expect(page.locator('.em-main tbody tr').first()).toBeVisible();
    });

    test.describe('di HP', () => {
        test.use({ viewport: { width: 375, height: 800 } });

        test('halaman portal, nilai, dan absensi siswa tanpa overflow horizontal', async ({ page }) => {
            await login(page, 'guru@madrasah.id', 'guru123');
            for (const url of ['/portal', '/nilai', '/absensi-siswa', '/absensi-siswa/rekap']) {
                await page.goto(url);
                const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
                expect(overflow, url).toBeLessThanOrEqual(0);
            }
        });
    });
});

test.describe('kenaikan kelas', () => {
    test('admin menaikkan satu siswa lalu membatalkannya', async ({ page }) => {
        await page.goto('/kenaikan-kelas');
        await page.selectOption('#kelas_id', { label: '7A' });
        await page.click('.em-main form[method="GET"] button[type="submit"]');

        const pilihan = page.locator('#formKenaikan tbody select');
        const jumlah = await pilihan.count();
        expect(jumlah).toBeGreaterThan(0);
        for (let i = 1; i < jumlah; i++) await pilihan.nth(i).selectOption('tunda');
        await page.selectOption('#kelas_tujuan_id', { index: 1 });

        page.once('dialog', (d) => d.accept());
        await page.click('#formKenaikan button[type="submit"]');
        await expect(page.locator('.em-alert-content')).toContainText('1 siswa berhasil diproses');
        await expect(page.getByText('Sudah diproses dari kelas ini')).toBeVisible();

        page.once('dialog', (d) => d.accept());
        await page.getByRole('button', { name: 'Batalkan' }).click();
        await expect(page.locator('.em-alert-content')).toContainText('Proses siswa dibatalkan');
    });
});

test.describe('raport: ekskul, kehadiran, catatan wali', () => {
    test('admin mengisi ekskul dan catatan, lalu data tampil lagi', async ({ page }) => {
        await page.goto('/raport');
        await page.getByRole('link', { name: 'Kelola Nilai' }).first().click();

        await page.fill('input[name="ekskul[0][nama]"]', 'Pramuka');
        await page.fill('input[name="ekskul[0][nilai]"]', 'A');
        await page.fill('input[name="sakit"]', '3');
        await page.fill('textarea[name="catatan_wali"]', 'Terus semangat belajar.');
        await page.click('form[action$="/pelengkap"] button[type="submit"]');

        await expect(page.locator('.em-alert-content')).toContainText('berhasil disimpan');
        await expect(page.locator('input[name="ekskul[0][nama]"]')).toHaveValue('Pramuka');
        await expect(page.locator('input[name="sakit"]')).toHaveValue('3');
        await expect(page.locator('textarea[name="catatan_wali"]')).toHaveValue('Terus semangat belajar.');
    });
});
