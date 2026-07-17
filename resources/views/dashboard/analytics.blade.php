@extends('layouts.app')

@section('title', 'Analitik Tren - Global SCM Risk Intel')

@section('content')
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-bold">Dasbor Analitik Tren Multi-Indikator</h2>
        <p class="text-muted-foreground">Visualisasi historis GDP, inflasi tahunan, fluktuasi valuta asing, dan tren total skor risiko rantai pasok.</p>
    </div>
    <select id="analyticsCountrySelect" class="input-skeuo sm:w-64 shrink-0">
        <option value="">-- Pilih Negara --</option>
        @foreach($countries as $c)
            <option value="{{ $c->iso2 }}" {{ $loop->first ? 'selected' : '' }}>
                {{ $c->name }} ({{ $c->iso2 }})
            </option>
        @endforeach
    </select>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="skeuo-card p-5">
        <h5 class="font-bold mb-4"><i class="fa-solid fa-chart-line text-primary mr-2"></i> Tren GDP (World Bank)</h5>
        <div class="relative" style="height: 250px;">
            <canvas id="gdpChart"></canvas>
        </div>
    </div>

    <div class="skeuo-card p-5">
        <h5 class="font-bold mb-4"><i class="fa-solid fa-chart-line text-warning mr-2"></i> Tren Inflasi Tahunan (%)</h5>
        <div class="relative" style="height: 250px;">
            <canvas id="inflationChart"></canvas>
        </div>
    </div>

    <div class="skeuo-card p-5">
        <h5 class="font-bold mb-4"><i class="fa-solid fa-chart-line text-success mr-2"></i> Tren Kurs Harian (vs USD)</h5>
        <div class="relative" style="height: 250px;">
            <canvas id="currencyChart"></canvas>
        </div>
    </div>

    <div class="skeuo-card p-5">
        <h5 class="font-bold mb-4"><i class="fa-solid fa-chart-line text-danger mr-2"></i> Riwayat Skor Risiko Rantai Pasok</h5>
        <div class="relative" style="height: 250px;">
            <canvas id="riskChart"></canvas>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const analyticsCountrySelect = document.getElementById('analyticsCountrySelect');

    // Chart instances
    let gdpChart = null;
    let inflationChart = null;
    let currencyChart = null;
    let riskChart = null;

    window.addEventListener('DOMContentLoaded', () => {
        if (analyticsCountrySelect.value) {
            analyticsCountrySelect.dispatchEvent(new Event('change'));
        }
    });

    analyticsCountrySelect.addEventListener('change', async function() {
        const iso = this.value;
        if (!iso) return;

        try {
            // Load Country details first to trigger refresh if missing/stale
            const cProfile = await apiFetch(`/api/countries/${iso}`);
            const currencyCode = cProfile.data.currency?.code;

            // 1. Load Economic indicators (GDP & Inflation)
            const indRes = await apiFetch(`/api/countries/${iso}/indicators`);
            if (indRes.success) {
                const indicators = indRes.data;

                const gdpData = indicators.gdp || [];
                const inflationData = indicators.inflation || [];

                renderLineChart('gdpChart', gdpChart, gdpData.map(d => d.year), gdpData.map(d => d.value / 1e9), 'GDP (Miliar USD)', '#0369a1', chart => gdpChart = chart);
                renderLineChart('inflationChart', inflationChart, inflationData.map(d => d.year), inflationData.map(d => d.value), 'Inflasi (%)', '#d97706', chart => inflationChart = chart);
            }

            // 2. Load Currency history
            if (currencyCode && currencyCode !== 'USD') {
                const currRes = await apiFetch(`/api/currency/${currencyCode}/history?days=30`);
                if (currRes.success) {
                    const cData = currRes.data;
                    renderLineChart('currencyChart', currencyChart, cData.map(d => d.rate_date), cData.map(d => d.rate_to_usd), `Kurs ${currencyCode} / USD`, '#16a34a', chart => currencyChart = chart);
                }
            } else {
                // USD is flat 1.0
                renderLineChart('currencyChart', currencyChart, [new Date().toDateString()], [1.0], 'Kurs USD / USD', '#16a34a', chart => currencyChart = chart);
            }

            // 3. Load Risk history
            const riskRes = await apiFetch(`/api/risk/${iso}/history?days=30`);
            if (riskRes.success) {
                const rData = riskRes.data;
                renderLineChart('riskChart', riskChart, rData.map(d => new Date(d.calculated_at).toLocaleDateString('id-ID')), rData.map(d => d.total_score), 'Skor Risiko', '#dc2626', chart => riskChart = chart);
            }

        } catch (err) {
            showToast(err.message || 'Gagal memuat data analitik tren.', 'error');
        }
    });

    function renderLineChart(canvasId, chartInstance, labels, data, label, color, setChartInstance) {
        const ctx = document.getElementById(canvasId).getContext('2d');
        if (chartInstance) {
            chartInstance.destroy();
        }

        const newChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: label,
                    data: data,
                    borderColor: color,
                    backgroundColor: 'rgba(15, 23, 42, 0.02)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { color: '#64748b', font: { family: 'Fira Sans', size: 11 } }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(15, 23, 42, 0.06)' },
                        ticks: { color: '#64748b', font: { family: 'Fira Sans', size: 10 } }
                    },
                    y: {
                        grid: { color: 'rgba(15, 23, 42, 0.06)' },
                        ticks: { color: '#64748b', font: { family: 'Fira Sans', size: 10 } }
                    }
                }
            }
        });

        setChartInstance(newChart);
    }
</script>
@endsection
