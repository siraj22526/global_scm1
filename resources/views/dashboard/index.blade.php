@extends('layouts.app')

@section('title', 'Country Dashboard - Global SCM Risk Intel')

@section('content')
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-bold">Dasbor Risiko Negara</h2>
        <p class="text-muted-foreground">Analisis profil umum, makroekonomi, cuaca, dan penilaian tingkat risiko global</p>
    </div>
    <div class="flex items-center gap-2 shrink-0">
        <select id="countrySelect" class="input-skeuo sm:w-64">
            <option value="">-- Pilih Negara --</option>
            @foreach($countries as $c)
                <option value="{{ $c->iso2 }}" {{ request('country') === $c->iso2 ? 'selected' : '' }}>
                    {{ $c->name }} ({{ $c->iso2 }})
                </option>
            @endforeach
        </select>
        <button id="watchlistBtn" class="btn-skeuo-outline hidden shrink-0">
            <i class="fa-regular fa-star"></i> <span class="hidden sm:inline">Tambah Watchlist</span>
        </button>
    </div>
</div>

<!-- Initial instructions if no country is selected -->
<div id="welcomePlaceholder" class="skeuo-card p-10 text-center">
    <i class="fa-solid fa-earth-americas text-primary text-5xl mb-4"></i>
    <h4 class="font-bold text-lg">Silakan Pilih Negara</h4>
    <p class="text-muted-foreground max-w-md mx-auto">Pilih salah satu negara dari dropdown di atas untuk memulai analisis risiko rantai pasok secara langsung.</p>
</div>

<!-- Main Dashboard Grid (Initially Hidden) -->
<div id="dashboardContent" class="hidden grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Column 1: Country Profile & General Metrics -->
    <div class="skeuo-card p-5">
        <div class="flex items-center gap-3 mb-4">
            <img id="countryFlag" src="" alt="Bendera" width="60" class="rounded-lg border border-border shadow-sm">
            <div>
                <h3 id="countryName" class="font-bold text-lg">Nama Negara</h3>
                <small id="countryOfficial" class="text-muted-foreground">Official Name</small>
            </div>
        </div>

        <hr class="border-border my-4">

        <div class="space-y-4">
            <div>
                <small class="text-muted-foreground block">Ibu Kota</small>
                <h5 id="countryCapital" class="font-semibold">Ibukota</h5>
            </div>
            <div>
                <small class="text-muted-foreground block">Wilayah</small>
                <h5 id="countryRegion" class="font-semibold">Region</h5>
            </div>
            <div>
                <small class="text-muted-foreground block">Mata Uang</small>
                <h5 id="countryCurrency" class="font-semibold">Currency</h5>
            </div>
            <div>
                <small class="text-muted-foreground block">Bahasa</small>
                <h5 id="countryLanguages" class="font-semibold">Languages</h5>
            </div>
        </div>
    </div>

    <!-- Column 2: Economic Metrics & Weather Snapshot -->
    <div class="flex flex-col gap-6">
        <div class="skeuo-card p-5">
            <h5 class="font-bold mb-4"><i class="fa-solid fa-chart-line text-primary mr-2"></i> Indikator Ekonomi</h5>

            <div class="flex justify-between mb-3">
                <span class="text-muted-foreground">Produk Domestik Bruto (GDP)</span>
                <span id="gdpVal" class="font-bold font-mono tabular-nums">--</span>
            </div>
            <div class="flex justify-between mb-3">
                <span class="text-muted-foreground">Tingkat Inflasi Tahunan</span>
                <span id="inflationVal" class="font-bold font-mono tabular-nums">--</span>
            </div>
            <div class="flex justify-between">
                <span class="text-muted-foreground">Populasi Penduduk</span>
                <span id="popVal" class="font-bold font-mono tabular-nums">--</span>
            </div>
        </div>

        <div class="skeuo-card p-5">
            <h5 class="font-bold mb-4"><i class="fa-solid fa-cloud-sun text-primary mr-2"></i> Kondisi Cuaca Ibu Kota</h5>

            <div class="flex justify-between mb-3">
                <span class="text-muted-foreground">Temperatur</span>
                <span id="tempVal" class="font-bold font-mono tabular-nums">--</span>
            </div>
            <div class="flex justify-between mb-3">
                <span class="text-muted-foreground">Curah Hujan</span>
                <span id="rainVal" class="font-bold font-mono tabular-nums">--</span>
            </div>
            <div class="flex justify-between">
                <span class="text-muted-foreground">Kecepatan Angin</span>
                <span id="windVal" class="font-bold font-mono tabular-nums">--</span>
            </div>
        </div>
    </div>

    <!-- Column 3: Risk Scoring & Verdict -->
    <div class="skeuo-card p-5 text-center flex flex-col justify-center">
        <h5 class="font-bold mb-2 text-left"><i class="fa-solid fa-triangle-exclamation text-primary mr-2"></i> Skor Risiko Rantai Pasok</h5>

        <div class="my-4">
            <small class="text-muted-foreground block mb-1">Skor Tertimbang</small>
            <h1 id="riskScoreVal" class="text-6xl font-bold mb-2 font-mono tabular-nums">0</h1>
            <span id="riskBadge" class="risk-low">LOW</span>
        </div>

        <p class="text-muted-foreground text-sm mt-3 px-3">Skor dihitung secara terbobot berdasarkan cuaca, sentimen berita, nilai inflasi, dan tingkat volatilitas kurs.</p>
    </div>
</div>

<!-- Skeleton Loading Dashboard Overlay -->
<div id="skeletonLoader" class="hidden grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="skeuo-card p-5 flex flex-col justify-between">
        <div class="skeleton skeleton-title"></div>
        <div>
            <div class="skeleton skeleton-text" style="width: 40%"></div>
            <div class="skeleton skeleton-text" style="width: 80%"></div>
        </div>
        <div class="mt-4">
            <div class="skeleton skeleton-text"></div>
            <div class="skeleton skeleton-text"></div>
            <div class="skeleton skeleton-text"></div>
        </div>
    </div>
    <div class="flex flex-col gap-6">
        <div class="skeuo-card p-5">
            <div class="skeleton skeleton-title" style="width: 30%"></div>
            <div class="skeleton skeleton-text"></div>
            <div class="skeleton skeleton-text"></div>
        </div>
        <div class="skeuo-card p-5">
            <div class="skeleton skeleton-title" style="width: 40%"></div>
            <div class="skeleton skeleton-text"></div>
            <div class="skeleton skeleton-text"></div>
        </div>
    </div>
    <div class="skeuo-card p-5 flex flex-col justify-center items-center">
        <div class="skeleton skeleton-title" style="width: 50%"></div>
        <div class="skeleton rounded-full" style="width: 100px; height: 100px; margin: 20px 0;"></div>
        <div class="skeleton skeleton-text" style="width: 70%"></div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const countrySelect = document.getElementById('countrySelect');
    const welcomePlaceholder = document.getElementById('welcomePlaceholder');
    const dashboardContent = document.getElementById('dashboardContent');
    const skeletonLoader = document.getElementById('skeletonLoader');
    const watchlistBtn = document.getElementById('watchlistBtn');

    let currentCountryIso = '';

    // Handle country selection
    countrySelect.addEventListener('change', async function() {
        const iso = this.value;
        if (!iso) {
            welcomePlaceholder.classList.remove('hidden');
            dashboardContent.classList.add('hidden');
            watchlistBtn.classList.add('hidden');
            return;
        }

        currentCountryIso = iso;
        welcomePlaceholder.classList.add('hidden');
        dashboardContent.classList.add('hidden');
        skeletonLoader.classList.remove('hidden');

        try {
            const res = await apiFetch(`/api/countries/${iso}`);
            if (res.success) {
                const data = res.data;

                // Set Country General Info
                document.getElementById('countryFlag').src = `https://flagcdn.com/w160/${data.iso2.toLowerCase()}.png`;
                document.getElementById('countryName').innerText = data.name;
                document.getElementById('countryOfficial').innerText = data.official_name || data.name;
                document.getElementById('countryCapital').innerText = data.capital || '--';
                document.getElementById('countryRegion').innerText = data.region || '--';
                document.getElementById('countryCurrency').innerText = data.currency ? `${data.currency.name} (${data.currency.code})` : '--';
                document.getElementById('countryLanguages').innerText = Object.values(data.languages || {}).join(', ') || '--';

                // Set Economic Indicators
                document.getElementById('gdpVal').innerText = data.gdp_usd ? `$${(data.gdp_usd / 1e12).toFixed(2)} Triliun` : '--';
                document.getElementById('inflationVal').innerText = data.inflation_pct !== null ? `${data.inflation_pct.toFixed(2)}%` : '--';
                document.getElementById('popVal').innerText = data.population ? `${(data.population / 1e6).toFixed(1)} Juta` : '--';

                // Set Weather Data
                if (data.weather) {
                    document.getElementById('tempVal').innerText = `${data.weather.temperature_c.toFixed(1)} °C`;
                    document.getElementById('rainVal').innerText = `${data.weather.precipitation_mm.toFixed(1)} mm`;
                    document.getElementById('windVal').innerText = `${data.weather.wind_speed_kmh.toFixed(1)} km/h`;
                } else {
                    document.getElementById('tempVal').innerText = '--';
                    document.getElementById('rainVal').innerText = '--';
                    document.getElementById('windVal').innerText = '--';
                }

                // Set Risk Info
                if (data.risk) {
                    const score = Math.round(data.risk.total_score);
                    document.getElementById('riskScoreVal').innerText = score;

                    const badge = document.getElementById('riskBadge');
                    badge.innerText = data.risk.level.toUpperCase();
                    badge.className = data.risk.level === 'high' ? 'risk-high' : (data.risk.level === 'medium' ? 'risk-medium' : 'risk-low');
                }

                // Toggle watchlist button
                watchlistBtn.classList.remove('hidden');
                try {
                    const wlRes = await apiFetch('/api/watchlist');
                    if (wlRes.success) {
                        const inWl = wlRes.data.some(w => w.iso2 === iso);
                        updateWatchlistBtnUI(inWl);
                    }
                } catch (e) {
                    // ignore minor error
                }

                skeletonLoader.classList.add('hidden');
                dashboardContent.classList.remove('hidden');
            }
        } catch (err) {
            showToast(err.message || 'Gagal memuat profil negara.', 'error');
            skeletonLoader.classList.add('hidden');
            welcomePlaceholder.classList.remove('hidden');
        }
    });

    // Helper for watchlist button UI
    function updateWatchlistBtnUI(isInWatchlist) {
        if (isInWatchlist) {
            watchlistBtn.innerHTML = '<i class="fa-solid fa-star text-warning"></i> <span class="hidden sm:inline">Terpantau</span>';
            watchlistBtn.dataset.active = 'true';
        } else {
            watchlistBtn.innerHTML = '<i class="fa-regular fa-star"></i> <span class="hidden sm:inline">Tambah Watchlist</span>';
            watchlistBtn.dataset.active = 'false';
        }
    }

    // Handle Watchlist actions
    watchlistBtn.addEventListener('click', async function() {
        const isActive = this.dataset.active === 'true';
        try {
            if (isActive) {
                const res = await apiFetch(`/api/watchlist/${currentCountryIso}`, { method: 'DELETE' });
                if (res.success) {
                    updateWatchlistBtnUI(false);
                }
            } else {
                const res = await apiFetch('/api/watchlist', {
                    method: 'POST',
                    body: { country_iso: currentCountryIso }
                });
                if (res.success) {
                    updateWatchlistBtnUI(true);
                }
            }
        } catch (err) {
            if (err.cancelled) return;
            console.error(err);
        }
    });

    // Auto-trigger if query param present
    window.addEventListener('DOMContentLoaded', () => {
        if (countrySelect.value) {
            countrySelect.dispatchEvent(new Event('change'));
        }
    });
</script>
@endsection
