import { execFileSync } from 'node:child_process';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import zlib from 'node:zlib';

export const PORT = 8099;
export const BASE_URL = `http://127.0.0.1:${PORT}`;

// Semua data tes E2E berada di folder sementara ini, bukan di database/storage pengembangan.
const ROOT = path.join(os.tmpdir(), 'emadrasah-e2e');
const STORAGE = path.join(ROOT, 'storage');
const DB_FILE = path.join(ROOT, 'e2e.sqlite');

/** Variabel lingkungan yang menimpa .env untuk server dan perintah artisan selama E2E. */
export const e2eEnv = {
    APP_ENV: 'local',
    APP_DEBUG: 'true',
    APP_URL: BASE_URL,
    DB_CONNECTION: 'sqlite',
    DB_DATABASE: DB_FILE,
    SESSION_DRIVER: 'file',
    CACHE_STORE: 'file',
    QUEUE_CONNECTION: 'sync',
    LOG_CHANNEL: 'single',
    LARAVEL_STORAGE_PATH: STORAGE,
    SESSION_SECURE_COOKIE: 'false',
    TRUSTED_PROXIES: '',
    MADRASAH_NAME: 'Madrasah Uji E2E',
    MADRASAH_SHORT_NAME: 'MU E2E',
    MADRASAH_FULL_NAME: 'Madrasah Uji E2E',
    MADRASAH_COLOR: '#047857',
};

function artisan(args, options = {}) {
    return execFileSync('php', ['artisan', ...args], {
        env: { ...process.env, ...e2eEnv },
        encoding: 'utf8',
        ...options,
    });
}

/** Membuat database SQLite baru lengkap dengan data demo, setelah memastikan targetnya aman. */
export function prepareApp() {
    if (!ROOT.startsWith(os.tmpdir()) || path.basename(ROOT) !== 'emadrasah-e2e') {
        throw new Error(`Folder E2E tidak aman: ${ROOT}`);
    }

    fs.rmSync(ROOT, { recursive: true, force: true });
    for (const dir of ['app/private', 'app/public', 'framework/cache/data', 'framework/sessions', 'framework/views', 'logs']) {
        fs.mkdirSync(path.join(STORAGE, dir), { recursive: true });
    }
    fs.writeFileSync(DB_FILE, '');

    // Pengaman: jangan lanjut kalau konfigurasi aktif BUKAN SQLite sementara ini
    // (mis. config:cache membuat variabel lingkungan diabaikan dan .env MySQL terpakai).
    const active = artisan(['tinker', '--execute=echo config("database.default")."|".config("database.connections.sqlite.database");']).trim();
    if (active !== `sqlite|${DB_FILE}`) {
        throw new Error(`Database aktif tidak sesuai target E2E (${active}). Dibatalkan agar data asli aman.`);
    }

    artisan(['migrate:fresh', '--seed', '--force'], { stdio: 'inherit' });
}

/** PNG polos berwarna (cukup untuk uji unggah logo; dibuat tanpa dependensi). */
export function pngBuffer(size = 96, [r, g, b] = [30, 64, 175]) {
    const crcTable = Array.from({ length: 256 }, (_, n) => {
        let c = n;
        for (let k = 0; k < 8; k++) c = c & 1 ? 0xedb88320 ^ (c >>> 1) : c >>> 1;
        return c >>> 0;
    });
    const crc = (buf) => {
        let c = 0xffffffff;
        for (const byte of buf) c = crcTable[(c ^ byte) & 0xff] ^ (c >>> 8);
        return (c ^ 0xffffffff) >>> 0;
    };
    const chunk = (type, data) => {
        const body = Buffer.concat([Buffer.from(type), data]);
        const out = Buffer.alloc(8 + data.length + 4);
        out.writeUInt32BE(data.length, 0);
        body.copy(out, 4);
        out.writeUInt32BE(crc(body), 8 + data.length);
        return out;
    };

    const ihdr = Buffer.alloc(13);
    ihdr.writeUInt32BE(size, 0);
    ihdr.writeUInt32BE(size, 4);
    ihdr.set([8, 2, 0, 0, 0], 8); // 8-bit RGB

    const row = Buffer.concat([Buffer.from([0]), Buffer.from(Array.from({ length: size }, () => [r, g, b]).flat())]);
    const raw = Buffer.concat(Array.from({ length: size }, () => row));

    return Buffer.concat([
        Buffer.from([0x89, 0x50, 0x4e, 0x47, 0x0d, 0x0a, 0x1a, 0x0a]),
        chunk('IHDR', ihdr),
        chunk('IDAT', zlib.deflateSync(raw)),
        chunk('IEND', Buffer.alloc(0)),
    ]);
}
