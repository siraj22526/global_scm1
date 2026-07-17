@extends('layouts.app')

@section('title', 'Peta Pelabuhan Dunia - Global SCM Risk Intel')

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
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-bold">Peta Pelabuhan Dunia (World Port Index)</h2>
        <p class="text-muted-foreground">Pemetaan geografis pelabuhan utama global, ukuran kapasitas dermaga, dan integrasi wilayah perdagangan.</p>
    </div>
    <div class="flex flex-col sm:flex-row gap-2 shrink-0">
        <input type="text" id="portSearch" class="input-skeuo sm:w-56" placeholder="Cari pelabuhan / kode WPI...">
        <select id="portCountry" class="input-skeuo sm:w-48">
            <option value="">Semua Negara</option>
            @foreach($countries as $c)
                <option value="{{ $c->iso2 }}">{{ $c->name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="skeuo-card p-3">
    <div id="map"></div>
</div>
@endsection

@section('scripts')
<script>
    let map;
    let markerCluster;
    const portSearch = document.getElementById('portSearch');
    const portCountry = document.getElementById('portCountry');

    window.addEventListener('DOMContentLoaded', () => {
        // Initialize Map
        map = L.map('map').setView([10.0, 100.0], 3);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        markerCluster = L.markerClusterGroup().addTo(map);

        // Fetch initial ports based on view bounds
        map.on('moveend', fetchPortsInBounds);

        // Listeners
        portSearch.addEventListener('input', debounce(fetchPortsInBounds, 500));
        portCountry.addEventListener('change', fetchPortsInBounds);

        // Fetch once
        fetchPortsInBounds();
    });

    async function fetchPortsInBounds() {
        const bounds = map.getBounds();
        const west = bounds.getWest();
        const south = bounds.getSouth();
        const east = bounds.getEast();
        const north = bounds.getNorth();

        const q = portSearch.value;
        const country = portCountry.value;

        // Construct query parameters
        let params = [];
        if (q) params.push(`q=${encodeURIComponent(q)}`);
        if (country) params.push(`country=${encodeURIComponent(country)}`);

        // Only query bounding box if we are zoomed in enough (to prevent massive loads) and not searching a specific country
        if (map.getZoom() > 4 && !country && !q) {
            params.push(`bbox=${west},${south},${east},${north}`);
        }

        const url = `/api/ports?${params.join('&')}`;

        try {
            const res = await apiFetch(url);
            if (res.success) {
                // Clear old markers
                markerCluster.clearLayers();

                const ports = res.data;
                ports.forEach(p => {
                    const marker = L.marker([p.latitude, p.longitude]);

                    const popupHtml = `
                        <div style="font-family: 'Fira Sans', sans-serif; min-width: 170px; color: #0f172a;">
                            <h6 style="margin: 0 0 4px; font-weight: 700;">${p.name}</h6>
                            <small style="color:#64748b; display:block; margin-bottom:8px;">Kode WPI: ${p.wpi_code || '--'}</small>
                            <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                                <span>Negara:</span> <strong>${p.country.name} (${p.country.iso2})</strong>
                            </div>
                            <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                                <span>Ukuran:</span> <strong>${p.harbor_size || 'Sedang'}</strong>
                            </div>
                            <div style="display:flex; justify-content:space-between;">
                                <span>Koordinat:</span> <strong style="font-size:0.8em;">${p.latitude.toFixed(4)}, ${p.longitude.toFixed(4)}</strong>
                            </div>
                        </div>
                    `;
                    marker.bindPopup(popupHtml);
                    markerCluster.addLayer(marker);
                });
            }
        } catch (err) {
            console.error(err);
        }
    }

    // Debounce helper for searching
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }
</script>
@endsection
