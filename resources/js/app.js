import * as bootstrap from 'bootstrap';
import '@fortawesome/fontawesome-free/css/all.min.css';
import '@fontsource-variable/inter/index.css';
import '@fontsource-variable/plus-jakarta-sans/index.css';

// Blade views call these as globals.
window.bootstrap = bootstrap;

// Chart.js is only needed on the dashboard, so it is loaded on demand.
window.loadChart = () =>
    import('chart.js/auto').then(({ default: Chart }) => (window.Chart = Chart));

/* ================================================================
   e-Madrasah · app.js  v4.0
   Satu sistem sidebar — em-sidebar only, tidak ada duplikasi
   ================================================================ */

(function () {
    'use strict';

    /* ── Konstanta ── */
    const LS_KEY    = 'em_sidebar_collapsed';
    const BP        = 992;  /* desktop breakpoint */

    /* ── Helpers ── */
    const $   = (sel, ctx = document) => ctx.querySelector(sel);
    const $$  = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];
    const isDesktop = () => window.innerWidth >= BP;

    /* ================================================================
       1. Sidebar
       ================================================================ */
    function initSidebar() {
        const sidebar = $('#emSidebar');
        const overlay = $('#sidebarOverlay');
        if (!sidebar) return;

        /* ── Sinkronkan keadaan ciut: hanya berlaku di desktop; di ponsel selalu lebar penuh ── */
        const wantsCollapsed = () => { try { return localStorage.getItem(LS_KEY) === 'true'; } catch (e) { return false; } };
        const btnDesktop = $('#sidebarToggleDesktop');

        /* Satu-satunya tempat yang memperbarui keadaan tombol (ARIA, label, tooltip) agar tidak pernah tidak sinkron. */
        function syncToggleState() {
            if (!btnDesktop) return;
            const collapsed = sidebar.classList.contains('is-collapsed');
            const label = collapsed ? 'Lebarkan menu' : 'Ciutkan menu';
            btnDesktop.setAttribute('aria-expanded', String(!collapsed));
            btnDesktop.setAttribute('aria-label', label);
            btnDesktop.setAttribute('title', label + ' (Ctrl+B)');
        }

        function syncCollapsed() {
            sidebar.classList.toggle('is-collapsed', isDesktop() && wantsCollapsed());
            syncToggleState();
        }
        syncCollapsed();

        function toggleDesktop() {
            if (!isDesktop()) return;
            const nowCollapsed = sidebar.classList.toggle('is-collapsed');
            try { localStorage.setItem(LS_KEY, nowCollapsed); } catch (e) {}
            syncToggleState();
            initTooltips(); /* tooltip menu hanya relevan saat diciutkan */
        }

        /* ── Desktop toggle ── */
        if (btnDesktop) {
            btnDesktop.addEventListener('click', toggleDesktop);
        }

        /* ── Pintasan Ctrl+B (Cmd+B di Mac), tidak aktif saat mengetik di kolom isian ── */
        document.addEventListener('keydown', e => {
            if (!(e.ctrlKey || e.metaKey) || e.key.toLowerCase() !== 'b' || e.altKey || e.shiftKey) return;
            const el = document.activeElement;
            if (el && (el.isContentEditable || /^(INPUT|TEXTAREA|SELECT)$/.test(el.tagName))) return;
            e.preventDefault();
            toggleDesktop();
        });

        /* ── Mobile FAB toggle ── */
        const btnMobile = $('#sidebarToggleMobile');
        if (btnMobile) {
            btnMobile.addEventListener('click', () => {
                if (isDesktop()) return;
                sidebar.classList.contains('is-open') ? closeMobile() : openMobile();
            });
        }

        /* ── Overlay click → tutup ── */
        if (overlay) {
            overlay.addEventListener('click', closeMobile);
        }

        /* ── Escape → tutup ── */
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && sidebar.classList.contains('is-open')) {
                closeMobile();
            }
        });

        /* ── Resize: bersihkan mobile state saat kembali ke desktop ── */
        window.addEventListener('resize', debounce(() => {
            if (isDesktop()) {
                sidebar.classList.remove('is-open');
                if (overlay) overlay.classList.remove('is-visible');
                document.body.style.overflow = '';
            }
            syncCollapsed();      /* ciut hanya di desktop; kembali ke ukuran penuh di ponsel */
            initTooltips();
        }, 150));

        /* ── Tombol tutup (ponsel) ── */
        const btnClose = $('#sidebarClose');
        if (btnClose) btnClose.addEventListener('click', closeMobile);

        /* ── Posisi sidebar: pulihkan gulir & pastikan menu aktif terlihat ── */
        const inner = $('.em-sidebar__inner', sidebar);
        if (inner) {
            try {
                const saved = parseInt(sessionStorage.getItem('em_sb_scroll') || '', 10);
                if (!Number.isNaN(saved)) inner.scrollTop = saved;
            } catch (e) {}
            const active = $('.em-nav__link.is-active', sidebar);
            if (active) {
                const a = active.getBoundingClientRect(), b = inner.getBoundingClientRect();
                if (a.top < b.top || a.bottom > b.bottom) active.scrollIntoView({ block: 'nearest' });
            }
            window.addEventListener('pagehide', () => {
                try { sessionStorage.setItem('em_sb_scroll', String(inner.scrollTop)); } catch (e) {}
            });
        }

        /* ── Tutup sidebar mobile saat klik nav link (UX nyaman) ── */
        $$('.em-nav__link', sidebar).forEach(link => {
            link.addEventListener('click', () => {
                if (!isDesktop()) closeMobile();
            });
        });

        /* ── Sidebar Dropdowns ── */
        $$('.em-nav__dropdown-toggle', sidebar).forEach(toggle => {
            const parent = toggle.closest('.em-nav__dropdown');
            const menu = parent && $('.em-nav__dropdown-menu', parent);
            const syncAria = () => toggle.setAttribute('aria-expanded', String(parent.classList.contains('is-open')));
            syncAria();

            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                if (parent) { parent.classList.toggle('is-open'); syncAria(); }
            });
            toggle.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggle.click(); }
                if (e.key === 'Escape') { toggle.blur(); }
            });

            /* Saat sidebar ciut, submenu tampil sebagai flyout di samping ikon: tentukan posisi vertikalnya */
            const placeFlyout = () => {
                if (!isDesktop() || !sidebar.classList.contains('is-collapsed') || !menu) return;
                const top = toggle.getBoundingClientRect().top;
                parent.style.setProperty('--em-flyout-top', top + 'px');
                requestAnimationFrame(() => {
                    const max = window.innerHeight - menu.offsetHeight - 12;
                    if (top > max) parent.style.setProperty('--em-flyout-top', Math.max(12, max) + 'px');
                });
            };
            if (parent) {
                parent.addEventListener('mouseenter', placeFlyout);
                parent.addEventListener('focusin', placeFlyout);
            }
        });

        function openMobile() {
            sidebar.classList.add('is-open');
            if (overlay) overlay.classList.add('is-visible');
            document.body.style.overflow = 'hidden';
        }

        function closeMobile() {
            sidebar.classList.remove('is-open');
            if (overlay) overlay.classList.remove('is-visible');
            document.body.style.overflow = '';
        }
    }

    /* ================================================================
       2. Active nav link (fallback JS — Blade sudah handle ini,
          ini backup untuk URL yang tidak match exact routeIs)
       ================================================================ */
    function initActiveNav() {
        const currentPath = window.location.pathname;
        $$('.em-nav__link').forEach(link => {
            if (link.classList.contains('is-active')) {
                /* Open parent dropdown if active link is inside one */
                const parentDropdown = link.closest('.em-nav__dropdown');
                if (parentDropdown) parentDropdown.classList.add('is-open');
                return;
            }
            const href = link.getAttribute('href');
            if (href && href !== '#' && href !== '/' && currentPath.startsWith(href)) {
                link.classList.add('is-active');
                const parentDropdown = link.closest('.em-nav__dropdown');
                if (parentDropdown) parentDropdown.classList.add('is-open');
            }
        });
    }

    /* ================================================================
       3. Bootstrap Tooltips
       ================================================================ */
    let activeTooltips = [];
    function initTooltips() {
        if (typeof bootstrap === 'undefined') return;
        
        /* Destroy existing to avoid duplicates */
        activeTooltips.forEach(t => t.dispose());
        activeTooltips = [];

        const sidebar = $('#emSidebar');
        const isCollapsed = sidebar && sidebar.classList.contains('is-collapsed');

        $$('[data-bs-toggle="tooltip"]').forEach(el => {
            /* Only show tooltips if sidebar is collapsed OR if it's not a sidebar link */
            const isSidebarLink = el.closest('.em-sidebar');
            if (isSidebarLink && !isCollapsed) return;

            const t = new bootstrap.Tooltip(el, { 
                trigger: 'hover',
                boundary: 'viewport'
            });
            activeTooltips.push(t);
        });
    }

    /* ================================================================
       4. Auto-dismiss alerts (5 detik)
       ================================================================ */
    function initAlerts() {
        $$('.em-alert:not(.alert-permanent)').forEach(el => {
            setTimeout(() => {
                if (typeof bootstrap !== 'undefined') {
                    try { 
                        const bsAlert = bootstrap.Alert.getOrCreateInstance(el);
                        if (bsAlert) bsAlert.close();
                    } catch (_) {}
                } else {
                    el.style.transition = 'opacity 0.4s, transform 0.4s';
                    el.style.opacity = '0';
                    el.style.transform = 'translateY(-6px)';
                    setTimeout(() => el.remove(), 420);
                }
            }, 5000);
        });
    }

    /* ================================================================
       5. Form validation (real-time + submit)
       ================================================================ */
    function initForms() {
        $$('form').forEach(form => {
            /* Real-time blur */
            $$('[required]', form).forEach(field => {
                field.addEventListener('blur', () => validateField(field));
                field.addEventListener('input', () => {
                    if (field.classList.contains('is-invalid')) validateField(field);
                });
            });

            /* Submit guard */
            form.addEventListener('submit', e => {
                let valid = true;
                $$('[required]', form).forEach(field => {
                    if (!validateField(field)) valid = false;
                });
                if (!valid) {
                    e.preventDefault();
                    showToast('Harap lengkapi semua field yang wajib diisi.', 'danger');
                    /* Scroll ke field pertama yang invalid */
                    const first = form.querySelector('.is-invalid');
                    if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        });
    }

    function validateField(field) {
        const ok = field.value.trim() !== '';
        field.classList.toggle('is-invalid', !ok);
        field.classList.toggle('is-valid',   ok);
        return ok;
    }

    /* ================================================================
       6. confirmDelete — modal konfirmasi hapus
       ================================================================ */
    window.confirmDelete = function (url, name = '') {
        /* Hapus modal lama jika ada */
        $('#em-confirm-modal')?.remove();

        const displayName = name
            ? `<strong>${escHtml(name)}</strong>`
            : 'data ini';

        const wrap = document.createElement('div');
        wrap.id = 'em-confirm-modal';
        wrap.innerHTML = `
            <div class="modal fade" tabindex="-1" id="emConfirmModalBs">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">
                        <div class="modal-body p-4 text-center">
                            <div style="width:54px;height:54px;border-radius:50%;background:#fee2e2;
                                        display:flex;align-items:center;justify-content:center;
                                        margin:0 auto 14px;">
                                <i class="fas fa-trash-alt text-danger" style="font-size:1.1rem;"></i>
                            </div>
                            <h6 class="fw-bold mb-1" style="font-size:0.95rem;">Konfirmasi Hapus</h6>
                            <p class="text-muted mb-0" style="font-size:0.82rem;">
                                Hapus ${displayName}? Tindakan ini tidak dapat dibatalkan.
                            </p>
                        </div>
                        <div class="modal-footer border-0 pt-0 pb-3 px-4 justify-content-center gap-2">
                            <button class="btn btn-outline-secondary btn-sm px-4"
                                    data-bs-dismiss="modal">Batal</button>
                            <button class="btn btn-danger btn-sm px-4" id="emConfirmOk">
                                Ya, Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;
        document.body.appendChild(wrap);

        const bsModal = new bootstrap.Modal($('#emConfirmModalBs'));
        bsModal.show();

        $('#emConfirmOk').addEventListener('click', () => {
            bsModal.hide();
            submitDelete(url);
        });
    };

    function submitDelete(url) {
        const f      = document.createElement('form');
        f.method     = 'POST';
        f.action     = url;
        const csrf   = inp('_token',  $('meta[name="csrf-token"]')?.content ?? '');
        const method = inp('_method', 'DELETE');
        f.append(csrf, method);
        document.body.appendChild(f);
        f.submit();
    }

    function inp(name, value) {
        return Object.assign(document.createElement('input'), {
            type: 'hidden', name, value
        });
    }

    function escHtml(str) {
        return str.replace(/[&<>"']/g, m => ({
            '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
        }[m]));
    }

    /* ================================================================
       7. showToast — toast notifikasi global
       ================================================================ */
    window.showToast = function (message, type = 'success') {
        let container = $('.toast-container.em-toast-container');
        if (!container) {
            container = Object.assign(document.createElement('div'), {
                className: 'toast-container em-toast-container position-fixed bottom-0 end-0 p-3',
            });
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }

        const icons = {
            success : 'fa-circle-check',
            danger  : 'fa-circle-xmark',
            warning : 'fa-triangle-exclamation',
            info    : 'fa-circle-info',
        };
        const icon = icons[type] ?? 'fa-circle-info';

        const wrap = document.createElement('div');
        wrap.innerHTML = `
            <div class="toast align-items-center border-0 shadow"
                 role="alert" data-bs-autohide="true" data-bs-delay="4500"
                 style="border-radius:12px;min-width:270px;overflow:hidden;">
                <div class="d-flex align-items-center gap-2 p-3">
                    <i class="fas ${icon} text-${type}" style="font-size:1rem;flex-shrink:0;"></i>
                    <div class="flex-grow-1 small fw-500">${message}</div>
                    <button type="button" class="btn-close btn-close-sm ms-auto"
                            data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>`;
        container.appendChild(wrap);

        const toast = new bootstrap.Toast(wrap.firstElementChild);
        toast.show();
        wrap.firstElementChild.addEventListener('hidden.bs.toast', () => wrap.remove());
    };

    /* ================================================================
       8. Fade-in animasi konten utama
       ================================================================ */
    function initFadeIn() {
        const main = $('#emMain') ?? $('main');
        if (main) main.classList.add('fade-in');
    }

    /* ================================================================
       Label ⇄ input: sambungkan <label> tanpa atribut for ke input terdekat,
       sehingga klik label memfokuskan input dan pembaca layar membacanya.
       ================================================================ */
    function initLabels() {
        let n = 0;
        const FIELD = 'input:not([type=hidden]):not([type=submit]):not([type=button]),select,textarea';
        $$('label:not([for])').forEach(label => {
            if (label.querySelector(FIELD)) return;           /* sudah membungkus input */
            let el = label.nextElementSibling;
            while (el && !el.matches(FIELD)) {
                const inner = el.querySelector ? el.querySelector(FIELD) : null;
                if (inner) { el = inner; break; }
                el = el.nextElementSibling;
            }
            if (!el || !el.matches(FIELD)) return;
            if (!el.id) el.id = 'f_' + (el.name || 'field').replace(/[^\w-]/g, '_') + '_' + (++n);
            label.setAttribute('for', el.id);
        });
    }

    /* ================================================================
       Utility: debounce
       ================================================================ */
    function debounce(fn, delay) {
        let t;
        return (...args) => {
            clearTimeout(t);
            t = setTimeout(() => fn(...args), delay);
        };
    }

    /* ================================================================
       Init
       ================================================================ */
    document.addEventListener('DOMContentLoaded', () => {
        initSidebar();
        initActiveNav();
        /* Sinkronkan aria-expanded setelah menu aktif dibuka oleh initActiveNav */
        $$('.em-nav__dropdown').forEach(d => {
            const t = $('.em-nav__dropdown-toggle', d);
            if (t) t.setAttribute('aria-expanded', String(d.classList.contains('is-open')));
        });
        initTooltips();
        initAlerts();
        initForms();
        initFadeIn();
        initLabels();
        /* Render pertama selesai -> aktifkan kembali transisi */
        requestAnimationFrame(() => requestAnimationFrame(() => document.body.classList.remove('em-preload')));
    });

})();

/* Tabel responsif: di HP, table[data-hide-sm] tampil sebagai kartu. Label tiap sel diambil dari judul kolom. */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('table[data-hide-sm]').forEach((table) => {
        const heads = [...table.querySelectorAll('thead tr:last-child > th')].map((th) => th.textContent.trim());

        table.querySelectorAll('tbody > tr').forEach((row) => {
            [...row.children].forEach((cell, i) => {
                const head = heads[i];
                if (!head || cell.hasAttribute('colspan') || cell.hasAttribute('data-label')) return;

                if (/^(no\.?|#)$/i.test(head)) {
                    cell.setAttribute('data-stack-hide', '');
                } else {
                    cell.setAttribute('data-label', head);
                }
            });
        });
    });
});

/* Umpan balik saat form dikirim: tombol submit dinonaktifkan dengan spinner agar tidak terkirim ganda.
   Hanya form POST. Lewati dengan data-no-loading pada <form>. */
document.addEventListener('submit', (event) => {
    const form = event.target;
    if (event.defaultPrevented) return; // dibatalkan handler lain (mis. dialog konfirmasi): jangan nonaktifkan tombol
    if (!(form instanceof HTMLFormElement) || form.method.toLowerCase() !== 'post' || form.hasAttribute('data-no-loading')) return;

    const button = event.submitter || form.querySelector('[type="submit"]');
    if (!button || button.disabled || button.dataset.loading) return;

    // Ditunda sampai data form selesai dibentuk, supaya nama/nilai tombol tetap terkirim.
    setTimeout(() => {
        button.dataset.loading = '1';
        button.dataset.original = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Memproses…';
    }, 0);
});

// Kembali lewat tombol "back" browser: pulihkan tombol yang masih berputar.
window.addEventListener('pageshow', (event) => {
    if (!event.persisted) return;
    document.querySelectorAll('[data-loading]').forEach((button) => {
        button.disabled = false;
        button.innerHTML = button.dataset.original;
        delete button.dataset.loading;
    });
});

/* Tombol yang hanya berisi ikon (aksi di tabel) diberi kelas btn-icon agar tampil sebagai lingkaran lembut.
   CSS murni tidak bisa membedakan "ikon saja" dari "ikon + teks". aria-label diambil dari title bila belum ada. */
function markIconButtons(root = document) {
    root.querySelectorAll('.btn:not(.btn-icon)').forEach((button) => {
        if (button.textContent.trim() !== '' || !button.querySelector('i, svg')) return;
        button.classList.add('btn-icon');
        if (!button.hasAttribute('aria-label') && button.title) {
            button.setAttribute('aria-label', button.title);
        }
    });
}
markIconButtons();
document.addEventListener('DOMContentLoaded', () => markIconButtons());
