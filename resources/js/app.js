import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// ---------------------------------------------------------------------
// CSRF-aware fetch helper used by every dashboard page's inline scripts.
// ---------------------------------------------------------------------
const csrfMeta = document.querySelector('meta[name="csrf-token"]');
const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

window.apiFetch = async function apiFetch(url, options = {}) {
    options.headers = options.headers || {};
    options.headers['X-CSRF-TOKEN'] = csrfToken;
    options.headers['Accept'] = 'application/json';

    if (options.body && !(options.body instanceof FormData) && typeof options.body === 'object') {
        options.headers['Content-Type'] = 'application/json';
        options.body = JSON.stringify(options.body);
    }

    const method = (options.method || 'GET').toUpperCase();

    // 1. Pre-request Confirmation Dialogs
    if (typeof Swal !== 'undefined') {
        if (method === 'DELETE') {
            const confirmResult = await Swal.fire({
                title: 'Konfirmasi Hapus',
                text: 'Apakah Anda yakin ingin menghapus data ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                background: '#ffffff',
                showClass: { popup: 'animate__animated animate__zoomIn animate__faster' },
                hideClass: { popup: 'animate__animated animate__fadeOut animate__faster' },
                customClass: {
                    popup: 'rounded-2xl border border-border shadow-2xl',
                    confirmButton: 'btn-skeuo !bg-none !bg-[var(--color-danger)] !shadow-none',
                    cancelButton: 'btn-skeuo-outline !shadow-none'
                }
            });
            if (!confirmResult.isConfirmed) {
                const cancelErr = new Error('cancelled');
                cancelErr.cancelled = true;
                throw cancelErr;
            }
        } else if (method === 'PUT' || method === 'PATCH') {
            const confirmResult = await Swal.fire({
                title: 'Konfirmasi Perubahan',
                text: 'Apakah Anda yakin ingin menyimpan perubahan data ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#0f766e',
                cancelButtonColor: '#64748b',
                background: '#ffffff',
                showClass: { popup: 'animate__animated animate__zoomIn animate__faster' },
                hideClass: { popup: 'animate__animated animate__fadeOut animate__faster' },
                customClass: {
                    popup: 'rounded-2xl border border-border shadow-2xl',
                    confirmButton: 'btn-skeuo !bg-none !bg-[var(--color-primary)] !shadow-none',
                    cancelButton: 'btn-skeuo-outline !shadow-none'
                }
            });
            if (!confirmResult.isConfirmed) {
                const cancelErr = new Error('cancelled');
                cancelErr.cancelled = true;
                throw cancelErr;
            }
        }
    }

    try {
        const response = await fetch(url, options);
        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.error?.message || result.message || 'Kesalahan Server Internal');
        }

        // 2. Post-request Success Dialogs
        if (typeof Swal !== 'undefined' && ['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)) {
            let title = 'Berhasil';
            let text = result.message || result.meta?.message || 'Aksi berhasil dilakukan.';
            
            if (method === 'DELETE') {
                title = 'Terhapus';
                text = result.message || result.meta?.message || 'Data berhasil dihapus.';
            } else if (method === 'PUT' || method === 'PATCH') {
                title = 'Disimpan';
                text = result.message || result.meta?.message || 'Perubahan berhasil disimpan.';
            } else if (method === 'POST') {
                title = 'Ditambahkan';
                text = result.message || result.meta?.message || 'Data berhasil ditambahkan/diproses.';
            }

            await Swal.fire({
                title: title,
                text: text,
                icon: 'success',
                confirmButtonColor: '#0f766e',
                background: '#ffffff',
                showClass: { popup: 'animate__animated animate__zoomIn animate__faster' },
                hideClass: { popup: 'animate__animated animate__fadeOut animate__faster' },
                customClass: {
                    popup: 'rounded-2xl border border-border shadow-2xl',
                    confirmButton: 'btn-skeuo !bg-none !bg-[var(--color-primary)] !shadow-none'
                }
            });
        }

        return result;
    } catch (err) {
        if (err.cancelled) {
            throw err;
        }

        // Handle failure dialog box
        if (typeof Swal !== 'undefined' && ['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)) {
            await Swal.fire({
                title: 'Gagal',
                text: err.message || 'Terjadi kesalahan saat memproses data.',
                icon: 'error',
                confirmButtonColor: '#dc2626',
                background: '#ffffff',
                showClass: { popup: 'animate__animated animate__zoomIn animate__faster' },
                hideClass: { popup: 'animate__animated animate__fadeOut animate__faster' },
                customClass: {
                    popup: 'rounded-2xl border border-border shadow-2xl',
                    confirmButton: 'btn-skeuo !bg-none !bg-[var(--color-danger)] !shadow-none'
                }
            });
        }

        console.error('API error fetching ' + url + ':', err);
        throw err;
    }
};

// ---------------------------------------------------------------------
// Toast notifications (replaces bootstrap.Toast)
// ---------------------------------------------------------------------
const toastRoot = document.getElementById('toastRoot');

window.showToast = function showToast(message, type = 'success') {
    if (!toastRoot) return;

    const colors = {
        success: 'border-l-4 border-l-[var(--color-success)]',
        error: 'border-l-4 border-l-[var(--color-danger)]',
        danger: 'border-l-4 border-l-[var(--color-danger)]',
        warning: 'border-l-4 border-l-[var(--color-warning)]',
    };

    const icons = {
        success: 'fa-circle-check text-[var(--color-success)]',
        error: 'fa-circle-exclamation text-[var(--color-danger)]',
        danger: 'fa-circle-exclamation text-[var(--color-danger)]',
        warning: 'fa-triangle-exclamation text-[var(--color-warning)]',
    };

    const el = document.createElement('div');
    el.className = `glass-card ${colors[type] || colors.success} flex items-start gap-3 px-4 py-3 shadow-lg opacity-0 translate-y-2 transition-all duration-300 max-w-sm`;
    el.innerHTML = `
        <i class="fa-solid ${icons[type] || icons.success} mt-0.5"></i>
        <p class="text-sm text-foreground flex-1">${message}</p>
        <button type="button" class="text-muted-foreground hover:text-foreground" aria-label="Tutup notifikasi">
            <i class="fa-solid fa-xmark"></i>
        </button>
    `;

    toastRoot.appendChild(el);
    requestAnimationFrame(() => {
        el.classList.remove('opacity-0', 'translate-y-2');
    });

    const dismiss = () => {
        el.classList.add('opacity-0', 'translate-y-2');
        setTimeout(() => el.remove(), 300);
    };

    el.querySelector('button').addEventListener('click', dismiss);
    setTimeout(dismiss, 4000);
};
