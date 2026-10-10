import { expect, test as setup } from '@playwright/test';

export const ADMIN_STATE = 'tests/e2e/.auth/admin.json';

// Masuk sekali, lalu semua tes memakai ulang sesi ini. Pembatas login (5 percobaan/menit per
// email+IP) sengaja aktif saat E2E, jadi tes tidak boleh masuk berulang kali sebagai akun yang sama.
setup('masuk sebagai admin', async ({ page }) => {
    await page.goto('/login');
    await page.fill('#email', 'admin@madrasah.id');
    await page.fill('#password', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard');
    await expect(page.locator('.dash-greeting__label')).toBeVisible();

    await page.context().storageState({ path: ADMIN_STATE });
});
