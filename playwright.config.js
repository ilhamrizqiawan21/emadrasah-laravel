import { defineConfig } from '@playwright/test';
import { BASE_URL, PORT, e2eEnv } from './tests/e2e/support.js';

// Tes end-to-end di Chrome yang terpasang (tanpa unduh browser).
// Server dan database sepenuhnya terpisah dari pengembangan: lihat tests/e2e/support.js.
export default defineConfig({
    testDir: './tests/e2e',
    globalSetup: './tests/e2e/global-setup.js',
    fullyParallel: false,
    workers: 1,
    retries: 0,
    timeout: 30_000,
    reporter: [['list']],
    use: {
        baseURL: BASE_URL,
        channel: 'chrome',
        screenshot: 'only-on-failure',
        trace: 'retain-on-failure',
    },
    projects: [
        { name: 'setup', testMatch: /auth\.setup\.js/ },
        {
            name: 'chrome',
            testMatch: /app\.spec\.js/,
            dependencies: ['setup'],
            // Semua tes berjalan sebagai admin yang sudah masuk, kecuali yang menyatakan lain.
            use: { storageState: 'tests/e2e/.auth/admin.json' },
        },
    ],
    webServer: {
        command: `php artisan serve --host=127.0.0.1 --port=${PORT}`,
        url: `${BASE_URL}/up`,
        env: e2eEnv,
        reuseExistingServer: false,
        timeout: 60_000,
    },
});
