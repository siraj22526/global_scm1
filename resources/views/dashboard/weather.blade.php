@extends('layouts.app')

@section('title', 'Peta Cuaca Global - Global SCM Risk Intel')

@section('styles')
<style>
    #map {
        height: 600px;
        width: 100%;
        background-color: #eef2f7;
    }
</style>
@endsection

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold">Peta Pemantauan Cuaca Global</h2>
    <p class="text-muted-foreground">Peta interaktif kondisi cuaca ibu kota negara dan estimasi risiko badai maritim.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-[2fr_1fr] gap-6">
    <div class="skeuo-card p-3">
        <div id="map"></div>
    </div>
    <div class="flex flex-col gap-6">
        <div class="skeuo-card p-5">
            <h5 class="font-bold mb-4">Legenda Risiko</h5>
            <div class="flex items-center gap-3 mb-4">
                <span class="inline-block rounded-full shrink-0" style="width: 16px; height: 16px; background-color: var(--color-success);"></span>
                <div>
                    <h6 class="font-semibold">Normal (Rendah)</h6>
                    <small class="text-muted-foreground">Risiko Badai 0 - 33</small>
                </div>
            </div>
            <div class="flex items-center gap-3 mb-4">
                <span class="inline-block rounded-full shrink-0" style="width: 16px; height: 16px; background-color: var(--color-warning);"></span>
                <div>
                    <h6 class="font-semibold">Waspada (Sedang)</h6>
                    <small class="text-muted-foreground">Risiko Badai 34 - 66</small>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-block rounded-full shrink-0" style="width: 16px; height: 16px; background-color: var(--color-danger);"></span>
                <div>
                    <h6 class="font-semibold">Awas (Tinggi)</h6>
                    <small class="text-muted-foreground">Risiko Badai 67 - 100</small>
                </div>
            </div>
        </div>

        <div class="skeuo-card p-5">
            <h5 class="font-bold mb-3">Informasi Cuaca</h5>
            <p class="text-sm text-muted-foreground">Marker warna mengikuti tingkat keparahan risiko badai (temperatur, curah hujan, kecepatan angin). Data di-caching selama 30 menit agar sistem responsif.</p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let map;

    window.addEventListener('DOMContentLoaded', async () => {
        // Initialize Map centered on global coordinates
        map = L.map('map').setView([15.0, 10.0], 2);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        try {
            // Fetch all countries list
            const res = await apiFetch('/api/countries');
            if (res.success) {
                const countries = res.data;

                for (const c of countries) {
                    // Fetch full info to get latest weather
                    const fullRes = await apiFetch(`/api/countries/${c.iso2}`);
                    if (fullRes.success && fullRes.data.weather) {
                        const data = fullRes.data;
                        const w = data.weather;
                        const r = data.risk;

                        // Select color based on storm risk
                        let markerColor = 'green';
                        if (r && r.total_score >= 67) {
                            markerColor = 'red';
                        } else if (r && r.total_score >= 34) {
                            markerColor = 'gold';
                        }

                        // Create custom circle marker
                        const marker = L.circleMarker([c.latitude, c.longitude], {
                            radius: 10,
                            fillColor: markerColor === 'red' ? '#dc2626' : (markerColor === 'gold' ? '#d97706' : '#16a34a'),
                            color: '#ffffff',
                            weight: 1.5,
                            fillOpacity: 0.85
                        }).addTo(map);

                        const badgeColor = r && r.level === 'high' ? '#dc2626' : (r && r.level === 'medium' ? '#d97706' : '#16a34a');

                        // Popup content
                        const popupHtml = `
                            <div style="font-family: 'Fira Sans', sans-serif; min-width: 160px; color: #0f172a;">
                                <h6 style="margin: 0 0 4px; font-weight: 700;">${c.name}</h6>
                                <small style="color:#64748b; display:block; margin-bottom:8px;">Ibu Kota: ${c.capital}</small>
                                <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                                    <span>Temp:</span> <strong>${w.temperature_c.toFixed(1)} °C</strong>
                                </div>
                                <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                                    <span>Hujan:</span> <strong>${w.precipitation_mm.toFixed(1)} mm</strong>
                                </div>
                                <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                                    <span>Angin:</span> <strong>${w.wind_speed_kmh.toFixed(1)} km/h</strong>
                                </div>
                                <div style="display:flex; justify-content:space-between; border-top:1px solid #e2e8f0; padding-top:6px; margin-top:6px;">
                                    <span>Risiko:</span>
                                    <strong style="color:${badgeColor};">${r ? r.level.toUpperCase() : 'UNKNOWN'}</strong>
                                </div>
                            </div>
                        `;
                        marker.bindPopup(popupHtml);
                    }
                }
            }
        } catch (err) {
            showToast('Gagal memuat data cuaca peta.', 'error');
            console.error(err);
        }
    });
</script>
@endsection
