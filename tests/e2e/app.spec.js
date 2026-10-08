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
