@extends('layouts.app')

@section('title', 'Daftar Pantauan - Global SCM Risk Intel')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold">Daftar Pantauan Risiko Anda</h2>
    <p class="text-muted-foreground">Negara yang Anda pilih untuk dipantau secara berkala (maksimal 20 negara).</p>
</div>

<div id="watchlistGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <div class="col-span-full text-center py-12">
        <div class="spinner text-primary mx-auto"></div>
        <p class="text-muted-foreground mt-3">Memuat daftar pantauan...</p>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const watchlistGrid = document.getElementById('watchlistGrid');

    window.addEventListener('DOMContentLoaded', () => {
        loadWatchlist();
    });

    async function loadWatchlist() {
        try {
            const res = await apiFetch('/api/watchlist');
            if (res.success) {
                const list = res.data;
                watchlistGrid.innerHTML = '';

                if (list.length === 0) {
                    watchlistGrid.innerHTML = `
                        <div class="col-span-full text-center py-12">
                            <div class="skeuo-card p-10 max-w-md mx-auto">
                                <i class="fa-regular fa-star text-muted-foreground text-5xl mb-4"></i>
                                <h4 class="font-bold text-lg">Daftar Pantauan Kosong</h4>
                                <p class="text-muted-foreground mb-4">Anda belum menambahkan negara apa pun ke daftar pantauan.</p>
                                <a href="{{ route('dashboard') }}" class="btn-skeuo inline-flex">Ke Dasbor Negara</a>
                            </div>
                        </div>
                    `;
                } else {
                    list.forEach(w => {
                        const col = document.createElement('div');

                        const score = w.risk ? Math.round(w.risk.total_score) : '--';
                        const level = w.risk ? w.risk.level.toUpperCase() : 'UNKNOWN';
                        const badgeClass = w.risk?.level === 'high' ? 'risk-high' : (w.risk?.level === 'medium' ? 'risk-medium' : 'risk-low');

                        col.innerHTML = `
                            <div class="skeuo-card p-5 h-full relative">
                                <button onclick="removeFromWatchlist('${w.iso2}')" class="absolute top-3 right-3 w-9 h-9 flex items-center justify-center rounded-lg text-danger hover:bg-danger/10" title="Hapus dari daftar pantauan">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                                <div class="flex items-center gap-3 mb-4 pr-8">
                                    <img src="${w.flag_url}" alt="Bendera ${w.name}" width="40" class="rounded-lg border border-border shadow-sm">
                                    <div>
                                        <h5 class="font-bold">${w.name}</h5>
                                        <small class="text-muted-foreground">${w.capital}</small>
                                    </div>
                                </div>
                                <div class="border-t border-border pt-3 mt-3 flex items-center justify-between">
                                    <span class="text-muted-foreground text-sm">Skor Risiko SCM</span>
                                    <div class="text-right">
                                        <strong class="text-xl block font-mono tabular-nums">${score}</strong>
                                        <span class="${badgeClass}">${level}</span>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <a href="{{ route('dashboard') }}?country=${w.iso2}" class="btn-skeuo-outline w-full">Buka Analisis</a>
                                </div>
                            </div>
                        `;
                        watchlistGrid.appendChild(col);
                    });
                }
            }
        } catch (err) {
            showToast(err.message || 'Gagal memuat daftar pantauan.', 'error');
        }
    }

    async function removeFromWatchlist(iso) {
        try {
            const res = await apiFetch(`/api/watchlist/${iso}`, { method: 'DELETE' });
            if (res.success) {
                loadWatchlist();
            }
        } catch (err) {
            // Silently catch cancel or handle other errors
            if (err.cancelled) return;
            console.error(err);
        }
    }
</script>
@endsection
