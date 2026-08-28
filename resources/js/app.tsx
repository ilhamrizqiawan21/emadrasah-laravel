import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import { ComponentType } from 'react';
import { createRoot } from 'react-dom/client';

type PageModule = {
    default: ComponentType<Record<string, unknown>>;
};

createInertiaApp({
    title: (title) => (title ? `${title} - eMadrasah` : 'eMadrasah'),
    resolve: (name) => {
        const pages = import.meta.glob<PageModule>('./Pages/**/*.tsx', { eager: true });
        const page = pages[`./Pages/${name}.tsx`];

        if (!page) {
            throw new Error(`Page not found: ${name}`);
        }

        return page;
    },
    setup({ el, App, props }) {
        if (!el) {
            return;
        }

        createRoot(el).render(<App {...props} />);
    },
    progress: {
        color: '#2563eb',
    },
});
