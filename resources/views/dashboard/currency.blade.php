@extends('layouts.app')

@section('title', 'Dampak Valuta - Global SCM Risk Intel')

@section('content')
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-bold">Dasbor Fluktuasi Nilai Tukar (Valuta)</h2>
        <p class="text-muted-foreground">Tren nilai tukar mata uang asing terhadap USD untuk melacak volatilitas biaya impor.</p>
    </div>
    <select id="currencySelect" class="input-skeuo sm:w-64 shrink-0">
        <option value="">-- Pilih Mata Uang --</option>
        @foreach($countries as $c)
            @if($c->currency && $c->currency->code !== 'USD')
                <option value="{{ $c->currency->code }}">
                    {{ $c->name }} - {{ $c->currency->code }} ({{ $c->currency->name }})
                </option>
            @endif
        @endforeach
    </select>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Chart Column -->
    <div class="skeuo-card p-5 lg:col-span-2">
        <h5 class="font-bold mb-4"><i class="fa-solid fa-chart-line text-primary mr-2"></i> Grafik Riwayat Nilai Tukar (30 Hari)</h5>
        <div id="chartContainer" class="relative" style="height: 350px; width: 100%;">
            <canvas id="historyChart"></canvas>
            <div id="chartPlaceholder" class="absolute inset-0 flex flex-col items-center justify-center text-center p-4">
                <i class="fa-solid fa-money-bill-trend-up text-muted-foreground text-4xl mb-3"></i>
                <p class="text-muted-foreground">Silakan pilih mata uang di kanan atas untuk memuat grafik riwayat fluktuasi.</p>
            </div>
        </div>
    </div>

    <!-- Currency Rates List Column -->
    <div class="skeuo-card p-5">
        <h5 class="font-bold mb-4"><i class="fa-solid fa-table-list text-primary mr-2"></i> Nilai Tukar Terkini (vs USD)</h5>
        <div class="overflow-y-auto" style="max-height: 380px;">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-muted-foreground uppercase text-xs tracking-wide border-b border-border">
                        <th class="py-2">Kode</th>
                        <th class="py-2">Mata Uang</th>
                        <th class="py-2 text-right">Nilai / 1 USD</th>
                    </tr>
                </thead>
                <tbody id="ratesTableBody">
                    <tr>
                        <td colspan="3" class="text-center text-muted-foreground py-6">
                            <div class="spinner spinner-sm text-primary inline-block mr-2"></div> Memuat nilai tukar...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const currencySelect = document.getElementById('currencySelect');
    const chartPlaceholder = document.getElementById('chartPlaceholder');
    const ratesTableBody = document.getElementById('ratesTableBody');

    let historyChart = null;

    window.addEventListener('DOMContentLoaded', async () => {
        // Load latest rates table
        try {
            const res = await apiFetch('/api/currency');
            if (res.success) {
                ratesTableBody.innerHTML = '';
                res.data.forEach(c => {
                    if (c.code === 'USD') return;
                    const row = document.createElement('tr');
                    row.className = 'border-t border-border';
                    row.innerHTML = `
                        <td class="py-2.5 font-semibold text-primary">${c.code}</td>
                        <td class="py-2.5 text-sm text-muted-foreground">${c.name}</td>
                        <td class="py-2.5 text-right font-bold font-mono tabular-nums">${c.rate_to_usd ? c.rate_to_usd.toFixed(4) : '--'} ${c.symbol || ''}</td>
                    `;
                    ratesTableBody.appendChild(row);
                });
            }
        } catch (err) {
            ratesTableBody.innerHTML = `<tr><td colspan="3" class="text-center text-danger py-6">${err.message || 'Gagal memuat kurs.'}</td></tr>`;
        }

        // Trigger selected param if any
        if (currencySelect.value) {
            currencySelect.dispatchEvent(new Event('change'));
        }
    });

    // Handle currency select change
    currencySelect.addEventListener('change', async function() {
        const code = this.value;
        if (!code) {
            chartPlaceholder.classList.remove('hidden');
            if (historyChart) {
                historyChart.destroy();
                historyChart = null;
            }
            return;
        }

        chartPlaceholder.classList.add('hidden');

        try {
            const res = await apiFetch(`/api/currency/${code}/history?days=30`);
            if (res.success) {
                const historyData = res.data;

                const labels = historyData.map(h => h.rate_date);
                const values = historyData.map(h => h.rate_to_usd);

                // Render Chart
                const ctx = document.getElementById('historyChart').getContext('2d');

                if (historyChart) {
                    historyChart.destroy();
                }

                historyChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: `Nilai Tukar ${code} per USD`,
                            data: values,
                            borderColor: '#0f766e',
                            backgroundColor: 'rgba(15, 118, 110, 0.08)',
                            borderWidth: 2.5,
                            fill: true,
                            tension: 0.2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    color: 'rgba(15, 23, 42, 0.06)'
                                },
                                ticks: {
                                    color: '#64748b',
                                    font: { family: 'Fira Sans' }
                                }
                            },
                            y: {
                                grid: {
                                    color: 'rgba(15, 23, 42, 0.06)'
                                },
                                ticks: {
                                    color: '#64748b',
                                    font: { family: 'Fira Sans' }
                                }
                            }
                        }
                    }
                });
            }
        } catch (err) {
            showToast(err.message || 'Gagal memuat grafik riwayat.', 'error');
        }
    });
</script>
@endsection
