@extends('layouts.app')

@section('title', 'Intelijen Berita - Global SCM Risk Intel')

@section('content')
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-bold">Intelijen Berita &amp; Analisis Sentimen</h2>
        <p class="text-muted-foreground">Pemindaian otomatis berita perdagangan global menggunakan sentimen berbasis leksikon.</p>
    </div>
    <div class="flex gap-2 shrink-0">
        <select id="newsCategory" class="input-skeuo sm:w-48">
            <option value="logistics">Logistik &amp; Distribusi</option>
            <option value="shipping">Pelayaran &amp; Maritim</option>
            <option value="trade">Perdagangan &amp; Ekspor</option>
            <option value="economy">Ekonomi Makro</option>
        </select>
        <select id="newsCountry" class="input-skeuo sm:w-40">
            <option value="">Global</option>
            @foreach($countries as $c)
                <option value="{{ $c->iso2 }}">{{ $c->name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Sentiment Aggregates Card -->
    <div class="flex flex-col gap-6">
        <div class="skeuo-card p-5">
            <h5 class="font-bold mb-4"><i class="fa-solid fa-chart-pie text-primary mr-2"></i> Agregat Sentimen</h5>

            <div class="mb-4">
                <div class="flex justify-between mb-1.5">
                    <span class="text-success text-sm font-semibold"><i class="fa-regular fa-face-smile mr-1"></i> Positif</span>
                    <strong id="posPct" class="font-mono tabular-nums">0%</strong>
                </div>
                <div class="progress-track">
                    <div id="posBar" class="progress-fill bg-success" style="width: 0%"></div>
                </div>
            </div>

            <div class="mb-4">
                <div class="flex justify-between mb-1.5">
                    <span class="text-muted-foreground text-sm font-semibold"><i class="fa-regular fa-face-meh mr-1"></i> Netral</span>
                    <strong id="neuPct" class="font-mono tabular-nums">0%</strong>
                </div>
                <div class="progress-track">
                    <div id="neuBar" class="progress-fill bg-muted-foreground" style="width: 0%"></div>
                </div>
            </div>

            <div>
                <div class="flex justify-between mb-1.5">
                    <span class="text-danger text-sm font-semibold"><i class="fa-regular fa-face-frown mr-1"></i> Negatif</span>
                    <strong id="negPct" class="font-mono tabular-nums">0%</strong>
                </div>
                <div class="progress-track">
                    <div id="negBar" class="progress-fill bg-danger" style="width: 0%"></div>
                </div>
            </div>
        </div>

        <div class="skeuo-card p-5">
            <h6 class="font-bold mb-2">Mengapa sentimen dihitung?</h6>
            <p class="text-sm text-muted-foreground">Rasio berita negatif pada topik geopolitik dan logistik (misal: "delay", "war", "shortage") dimasukkan ke dalam Risk Scoring Engine untuk memperkirakan kerawanan jalur rantai pasok secara keseluruhan.</p>
        </div>
    </div>

    <!-- News List Column -->
    <div class="lg:col-span-2">
        <div class="skeuo-card p-5">
            <h5 class="font-bold mb-4"><i class="fa-solid fa-list-check text-primary mr-2"></i> Daftar Artikel Terindeks</h5>

            <div id="newsContainer" class="flex flex-col gap-4">
                <div class="skeleton rounded-xl" style="height: 100px;"></div>
                <div class="skeleton rounded-xl" style="height: 100px;"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const newsCategory = document.getElementById('newsCategory');
    const newsCountry = document.getElementById('newsCountry');
    const newsContainer = document.getElementById('newsContainer');

    window.addEventListener('DOMContentLoaded', () => {
        loadNewsAndSummary();
    });

    newsCategory.addEventListener('change', loadNewsAndSummary);
    newsCountry.addEventListener('change', loadNewsAndSummary);

    async function loadNewsAndSummary() {
        const cat = newsCategory.value;
        const country = newsCountry.value;

        // Render Skeletons
        newsContainer.innerHTML = `
            <div class="skeleton rounded-xl" style="height: 120px;"></div>
            <div class="skeleton rounded-xl" style="height: 120px;"></div>
        `;

        try {
            // 1. Fetch news articles
            let url = `/api/news?category=${cat}`;
            if (country) {
                url += `&country=${country}`;
            }

            const newsRes = await apiFetch(url);
            if (newsRes.success) {
                newsContainer.innerHTML = '';
                const articles = newsRes.data;

                if (articles.length === 0) {
                    newsContainer.innerHTML = `
                        <div class="text-center py-12 text-muted-foreground">
                            <i class="fa-regular fa-newspaper text-4xl mb-3"></i>
                            <p class="font-semibold mb-1">Berita tidak tersedia saat ini</p>
                            <p class="text-sm">Pastikan GNEWS_API_KEY sudah dikonfigurasi di server, atau coba kategori/negara lain.</p>
                        </div>
                    `;
                } else {
                    articles.forEach(art => {
                        const card = document.createElement('div');
                        card.className = 'skeuo-card p-4';

                        let badgeClasses = 'bg-muted text-muted-foreground';
                        if (art.sentiment?.label === 'positive') badgeClasses = 'bg-success/10 text-success';
                        else if (art.sentiment?.label === 'negative') badgeClasses = 'bg-danger/10 text-danger';

                        const pubDate = new Date(art.published_at).toLocaleDateString('id-ID', {
                            day: 'numeric',
                            month: 'short',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });

                        const thumbnail = art.image_url
                            ? `<img src="${art.image_url}" alt="" class="w-full sm:w-32 h-32 sm:h-full object-cover rounded-xl shrink-0" loading="lazy" onerror="this.remove()">`
                            : '';

                        card.innerHTML = `
                            <div class="flex flex-col sm:flex-row gap-4">
                                ${thumbnail}
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold uppercase ${badgeClasses}">
                                            <i class="fa-solid fa-brain"></i> ${art.sentiment?.label.toUpperCase() || 'NEUTRAL'}
                                        </span>
                                        <small class="text-muted-foreground">${pubDate}</small>
                                    </div>
                                    <h5 class="font-bold mb-2">
                                        <a href="${art.url}" target="_blank" rel="noopener" class="hover:text-primary">${art.title}</a>
                                    </h5>
                                    <p class="text-muted-foreground text-sm mb-2">${art.description || 'Tidak ada deskripsi singkat.'}</p>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-primary"><i class="fa-solid fa-tags mr-1"></i> ${art.category}</span>
                                        <small class="text-muted-foreground"><i class="fa-solid fa-chart-bar mr-1"></i> Positif: ${art.sentiment?.positive || 0} | Negatif: ${art.sentiment?.negative || 0}</small>
                                    </div>
                                </div>
                            </div>
                        `;
                        newsContainer.appendChild(card);
                    });
                }
            }

            // 2. Fetch aggregates
            let summaryUrl = `/api/news/summary?`;
            if (cat) summaryUrl += `category=${cat}&`;
            if (country) summaryUrl += `country=${country}`;

            const summaryRes = await apiFetch(summaryUrl);
            if (summaryRes.success) {
                const s = summaryRes.data;
                document.getElementById('posPct').innerText = `${s.positive_pct}%`;
                document.getElementById('posBar').style.width = `${s.positive_pct}%`;

                document.getElementById('neuPct').innerText = `${s.neutral_pct}%`;
                document.getElementById('neuBar').style.width = `${s.neutral_pct}%`;

                document.getElementById('negPct').innerText = `${s.negative_pct}%`;
                document.getElementById('negBar').style.width = `${s.negative_pct}%`;
            }
        } catch (err) {
            showToast(err.message || 'Gagal memuat berita.', 'error');
        }
    }
</script>
@endsection
