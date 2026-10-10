import { prepareApp } from './support.js';

// Dijalankan sekali sebelum server E2E menyala: database dan storage sementara disiapkan di sini.
export default async function globalSetup() {
    prepareApp();
}
