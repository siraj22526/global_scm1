@extends('layouts.app')

@section('title', 'Komparasi Negara - Global SCM Risk Intel')

@section('content')
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-bold">Komparasi Risiko Negara</h2>
        <p class="text-muted-foreground">Bandingkan profil ekonomi, cuaca, dan kerentanan rantai pasok secara berdampingan.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2 shrink-0">
        <select id="countryA" class="input-skeuo" style="max-width: 180px;">
            <option value="">-- Negara A --</option>
            @foreach($countries as $c)
                <option value="{{ $c->iso2 }}">A - {{ $c->name }}</option>
            @endforeach
        </select>
        <span class="text-muted-foreground font-bold">VS</span>
        <select id="countryB" class="input-skeuo" style="max-width: 180px;">
            <option value="">-- Negara B --</option>
            @foreach($countries as $c)
                <option value="{{ $c->iso2 }}">B - {{ $c->name }}</option>
            @endforeach
        </select>
        <button id="compareBtn" class="btn-skeuo">Bandingkan</button>
    </div>
</div>

<!-- Comparison Placeholder -->
<div id="comparePlaceholder" class="skeuo-card p-10 text-center">
    <i class="fa-solid fa-scale-balanced text-primary text-5xl mb-4"></i>
    <h4 class="font-bold text-lg">Silakan Pilih Dua Negara</h4>
    <p class="text-muted-foreground max-w-md mx-auto">Pilih kedua negara yang ingin dibandingkan pada menu di atas lalu klik tombol "Bandingkan".</p>
</div>

<!-- Comparison Table Grid -->
<div id="compareResult" class="skeuo-card p-5 hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-muted-foreground border-b border-border">
                    <th class="py-2.5" style="width: 34%;">Parameter Analisis</th>
                    <th id="nameHeaderA" class="py-2.5 text-center" style="width: 33%;">Negara A</th>
                    <th id="nameHeaderB" class="py-2.5 text-center" style="width: 33%;">Negara B</th>
                </tr>
            </thead>
            <tbody>
                <!-- Profile -->
                <tr><td colspan="3" class="pt-4 pb-1 text-xs text-primary font-bold uppercase">Profil Umum</td></tr>
                <tr class="border-t border-border">
                    <td class="py-2">Ibu Kota</td>
                    <td id="capitalA" class="py-2 text-center">--</td>
                    <td id="capitalB" class="py-2 text-center">--</td>
                </tr>
                <tr class="border-t border-border">
                    <td class="py-2">Wilayah (Region)</td>
                    <td id="regionA" class="py-2 text-center">--</td>
                    <td id="regionB" class="py-2 text-center">--</td>
                </tr>
                <tr class="border-t border-border">
                    <td class="py-2">Mata Uang (Currency)</td>
                    <td id="currencyA" class="py-2 text-center">--</td>
                    <td id="currencyB" class="py-2 text-center">--</td>
                </tr>

                <!-- Economic indicators -->
                <tr><td colspan="3" class="pt-4 pb-1 text-xs text-primary font-bold uppercase">Makroekonomi (World Bank)</td></tr>
                <tr id="rowGdp" class="border-t border-border">
                    <td class="py-2">GDP Tahunan (USD)</td>
                    <td id="gdpA" class="py-2 text-center font-mono tabular-nums">--</td>
                    <td id="gdpB" class="py-2 text-center font-mono tabular-nums">--</td>
                </tr>
                <tr id="rowInflation" class="border-t border-border">
                    <td class="py-2">Tingkat Inflasi Tahunan (%)</td>
                    <td id="inflationA" class="py-2 text-center font-mono tabular-nums">--</td>
                    <td id="inflationB" class="py-2 text-center font-mono tabular-nums">--</td>
                </tr>
                <tr id="rowPopulation" class="border-t border-border">
                    <td class="py-2">Jumlah Populasi Penduduk</td>
                    <td id="populationA" class="py-2 text-center font-mono tabular-nums">--</td>
                    <td id="populationB" class="py-2 text-center font-mono tabular-nums">--</td>
                </tr>
                <tr id="rowExport" class="border-t border-border">
                    <td class="py-2">Volume Ekspor Tahunan (USD)</td>
                    <td id="exportA" class="py-2 text-center font-mono tabular-nums">--</td>
                    <td id="exportB" class="py-2 text-center font-mono tabular-nums">--</td>
                </tr>
                <tr id="rowImport" class="border-t border-border">
                    <td class="py-2">Volume Impor Tahunan (USD)</td>
                    <td id="importA" class="py-2 text-center font-mono tabular-nums">--</td>
                    <td id="importB" class="py-2 text-center font-mono tabular-nums">--</td>
                </tr>

                <!-- Weather metrics -->
                <tr><td colspan="3" class="pt-4 pb-1 text-xs text-primary font-bold uppercase">Kondisi Cuaca &amp; Badai (Open-Meteo)</td></tr>
                <tr class="border-t border-border">
                    <td class="py-2">Temperatur saat ini</td>
                    <td id="tempA" class="py-2 text-center font-mono tabular-nums">--</td>
                    <td id="tempB" class="py-2 text-center font-mono tabular-nums">--</td>
                </tr>
                <tr id="rowPrecipitation" class="border-t border-border">
                    <td class="py-2">Curah Hujan (Curah air)</td>
                    <td id="precipA" class="py-2 text-center font-mono tabular-nums">--</td>
                    <td id="precipB" class="py-2 text-center font-mono tabular-nums">--</td>
                </tr>
                <tr id="rowWind" class="border-t border-border">
                    <td class="py-2">Kecepatan Angin rata-rata</td>
                    <td id="windA" class="py-2 text-center font-mono tabular-nums">--</td>
                    <td id="windB" class="py-2 text-center font-mono tabular-nums">--</td>
                </tr>
                <tr id="rowStorm" class="border-t border-border">
                    <td class="py-2">Indeks Risiko Badai (0 - 100)</td>
                    <td id="stormA" class="py-2 text-center font-mono tabular-nums">--</td>
                    <td id="stormB" class="py-2 text-center font-mono tabular-nums">--</td>
                </tr>

                <!-- Final SCM Risk Score -->
                <tr><td colspan="3" class="pt-4 pb-1 text-xs text-primary font-bold uppercase">Penilaian Risiko Gabungan</td></tr>
                <tr id="rowRisk" class="border-t border-border">
                    <td class="py-3 font-bold">Total Skor Risiko SCM</td>
                    <td id="riskA" class="py-3 text-center font-bold font-mono tabular-nums">--</td>
                    <td id="riskB" class="py-3 text-center font-bold font-mono tabular-nums">--</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const countryASelect = document.getElementById('countryA');
    const countryBSelect = document.getElementById('countryB');
    const compareBtn = document.getElementById('compareBtn');
    const comparePlaceholder = document.getElementById('comparePlaceholder');
    const compareResult = document.getElementById('compareResult');

    compareBtn.addEventListener('click', async () => {
        const a = countryASelect.value;
        const b = countryBSelect.value;

        if (!a || !b) {
            showToast('Silakan pilih kedua negara terlebih dahulu.', 'error');
            return;
        }

        if (a === b) {
            showToast('Harap pilih dua negara yang berbeda.', 'warning');
            return;
        }

        comparePlaceholder.classList.add('hidden');
        compareResult.classList.add('hidden');

        try {
            const res = await apiFetch(`/api/compare?a=${a}&b=${b}`);
            if (res.success) {
                const data = res.data;
                const ca = data.country_a;
                const cb = data.country_b;

                // Headers flag + name
                document.getElementById('nameHeaderA').innerHTML = `<img src="https://flagcdn.com/w40/${ca.iso2.toLowerCase()}.png" class="inline mr-2 rounded shadow-sm border border-border"> ${ca.name}`;
                document.getElementById('nameHeaderB').innerHTML = `<img src="https://flagcdn.com/w40/${cb.iso2.toLowerCase()}.png" class="inline mr-2 rounded shadow-sm border border-border"> ${cb.name}`;

                // General Profile
                document.getElementById('capitalA').innerText = ca.capital || '--';
                document.getElementById('capitalB').innerText = cb.capital || '--';
                document.getElementById('regionA').innerText = ca.region || '--';
                document.getElementById('regionB').innerText = cb.region || '--';
                document.getElementById('currencyA').innerText = ca.currency ? `${ca.currency.name} (${ca.currency.code})` : '--';
                document.getElementById('currencyB').innerText = cb.currency ? `${cb.currency.name} (${cb.currency.code})` : '--';

                // Macroeconomic indicators
                document.getElementById('gdpA').innerText = ca.gdp ? `$${(ca.gdp / 1e9).toFixed(2)} Miliar` : '--';
                document.getElementById('gdpB').innerText = cb.gdp ? `$${(cb.gdp / 1e9).toFixed(2)} Miliar` : '--';
                document.getElementById('inflationA').innerText = ca.inflation !== null ? `${ca.inflation.toFixed(2)}%` : '--';
                document.getElementById('inflationB').innerText = cb.inflation !== null ? `${cb.inflation.toFixed(2)}%` : '--';
                document.getElementById('populationA').innerText = ca.population ? `${(ca.population / 1e6).toFixed(1)} Juta` : '--';
                document.getElementById('populationB').innerText = cb.population ? `${(cb.population / 1e6).toFixed(1)} Juta` : '--';
                document.getElementById('exportA').innerText = ca.export ? `$${(ca.export / 1e9).toFixed(2)} Miliar` : '--';
                document.getElementById('exportB').innerText = cb.export ? `$${(cb.export / 1e9).toFixed(2)} Miliar` : '--';
                document.getElementById('importA').innerText = ca.import ? `$${(ca.import / 1e9).toFixed(2)} Miliar` : '--';
                document.getElementById('importB').innerText = cb.import ? `$${(cb.import / 1e9).toFixed(2)} Miliar` : '--';

                // Weather
                document.getElementById('tempA').innerText = ca.weather ? `${ca.weather.temperature_c.toFixed(1)} °C` : '--';
                document.getElementById('tempB').innerText = cb.weather ? `${cb.weather.temperature_c.toFixed(1)} °C` : '--';
                document.getElementById('precipA').innerText = ca.weather ? `${ca.weather.precipitation_mm.toFixed(1)} mm` : '--';
                document.getElementById('precipB').innerText = cb.weather ? `${cb.weather.precipitation_mm.toFixed(1)} mm` : '--';
                document.getElementById('windA').innerText = ca.weather ? `${ca.weather.wind_speed_kmh.toFixed(1)} km/h` : '--';
                document.getElementById('windB').innerText = cb.weather ? `${cb.weather.wind_speed_kmh.toFixed(1)} km/h` : '--';
                document.getElementById('stormA').innerText = ca.weather ? ca.weather.storm_risk : '--';
                document.getElementById('stormB').innerText = cb.weather ? cb.weather.storm_risk : '--';

                // Risk
                document.getElementById('riskA').innerHTML = ca.risk ? `${Math.round(ca.risk.total_score)} <span class="risk-${ca.risk.level} ml-2">${ca.risk.level}</span>` : '--';
                document.getElementById('riskB').innerHTML = cb.risk ? `${Math.round(cb.risk.total_score)} <span class="risk-${cb.risk.level} ml-2">${cb.risk.level}</span>` : '--';

                // Clear highlights
                const tds = compareResult.querySelectorAll('td');
                tds.forEach(td => td.classList.remove('text-success', 'font-bold'));

                // Apply dynamic highlights (higher GDP/exports, lower inflation/risk score/storm risk index)
                highlightBetterValue(ca.gdp, cb.gdp, 'gdpA', 'gdpB', true);
                highlightBetterValue(ca.inflation, cb.inflation, 'inflationA', 'inflationB', false);
                highlightBetterValue(ca.export, cb.export, 'exportA', 'exportB', true);
                highlightBetterValue(ca.import, cb.import, 'importA', 'importB', true);
                highlightBetterValue(ca.weather?.precipitation_mm, cb.weather?.precipitation_mm, 'precipA', 'precipB', false);
                highlightBetterValue(ca.weather?.wind_speed_kmh, cb.weather?.wind_speed_kmh, 'windA', 'windB', false);
                highlightBetterValue(ca.weather?.storm_risk, cb.weather?.storm_risk, 'stormA', 'stormB', false);
                highlightBetterValue(ca.risk?.total_score, cb.risk?.total_score, 'riskA', 'riskB', false);

                compareResult.classList.remove('hidden');
            }
        } catch (err) {
            showToast(err.message || 'Gagal memuat komparasi.', 'error');
        }
    });

    function highlightBetterValue(valA, valB, idA, idB, higherIsBetter) {
        if (valA === null || valB === null || valA === undefined || valB === undefined) return;
        const elA = document.getElementById(idA);
        const elB = document.getElementById(idB);

        if (valA === valB) return;

        if (higherIsBetter) {
            if (valA > valB) elA.classList.add('text-success', 'font-bold');
            else elB.classList.add('text-success', 'font-bold');
        } else {
            if (valA < valB) elA.classList.add('text-success', 'font-bold');
            else elB.classList.add('text-success', 'font-bold');
        }
    }
</script>
@endsection
