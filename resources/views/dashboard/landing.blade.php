@extends('layouts.guest')

@section('title', 'Global Supply Chain Risk Intelligence Platform')

@section('content')
<!-- Hero Section (Full Screen Viewport) -->
<div class="relative overflow-hidden bg-gradient-to-tr from-[#f4f7fb] via-[#eef2f7] to-[#e0e7ff] min-h-[calc(100vh-4rem)] flex flex-col justify-center border-b border-border">
    <!-- Ambient background glowing points -->
    <div class="absolute top-1/4 left-1/10 w-96 h-96 bg-primary/10 rounded-full blur-3xl pointer-events-none animate-pulse"></div>
    <div class="absolute bottom-1/4 right-1/10 w-80 h-80 bg-teal-400/10 rounded-full blur-3xl pointer-events-none animate-pulse"></div>
    <!-- Clean grid overlay -->
    <div class="absolute inset-0 bg-[linear-gradient(rgba(20,184,166,0.015)_1px,transparent_1px),linear-gradient(90deg,rgba(20,184,166,0.015)_1px,transparent_1px)] bg-[size:24px_24px] pointer-events-none z-0"></div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20 w-full z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 items-center gap-12">
            <!-- Left Headline Column -->
            <div class="lg:col-span-7 animate-slide-up space-y-6">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary/10 border border-primary/20 text-primary text-xs font-bold uppercase tracking-wider">
                    <i class="fa-solid fa-circle-nodes animate-pulse"></i> Intelligent Decision Support
                </div>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold leading-tight tracking-tight text-[#0f172a]">
                    Pantau Risiko Rantai Pasok <br>
                    <span class="bg-gradient-to-r from-primary via-teal-600 to-sky-600 bg-clip-text text-transparent">Secara Real-Time & Presisi</span>
                </h1>
                <p class="text-lg text-slate-600 max-w-xl leading-relaxed">
                    Integrasikan intelijen cuaca global ekstrem, volatilitas valuta asing, tingkat inflasi, dan sentimen berita geopolitik ke dalam dasbor analitis interaktif untuk kelancaran logistik Anda.
                </p>
                <div class="flex flex-wrap gap-4 pt-2">
                    <a href="{{ route('login') }}" class="btn-skeuo !px-8 !py-3.5 !text-base group hover:scale-[1.02] active:scale-95 transition-all">
                        <i class="fa-solid fa-right-to-bracket transition-transform group-hover:translate-x-1"></i> Masuk ke Dasbor
                    </a>
                    <a href="{{ route('register') }}" class="btn-skeuo-outline !px-8 !py-3.5 !text-base hover:scale-[1.02] active:scale-95 transition-all">
                        Daftar Akun Baru
                    </a>
                </div>
            </div>

            <!-- Right Interactive Graphic Column -->
            <div class="lg:col-span-5 flex justify-center relative min-h-[400px]">
                <!-- Outer floating abstract circle -->
                <div class="absolute w-80 h-80 rounded-full border border-primary/10 animate-[spin_30s_linear_infinite] pointer-events-none z-0"></div>
                
                <div class="relative w-full max-w-sm z-10 flex flex-col justify-center space-y-6">
                    <!-- Weather Card -->
                    <div class="skeuo-card p-6 text-left transform -rotate-2 hover:rotate-0 hover:scale-[1.02] transition-all duration-300 shadow-2xl bg-white/70 backdrop-blur-md border border-white/40">
                        <div class="flex items-center gap-3.5 mb-3">
                            <div class="bg-primary/10 p-3 rounded-2xl">
                                <i class="fa-solid fa-cloud-bolt text-primary text-2xl"></i>
                            </div>
                            <div>
                                <h6 class="font-bold text-base text-[#0f172a]">Pemantauan Cuaca Ekstrem</h6>
                                <small class="text-muted-foreground font-semibold">Open-Meteo Integration</small>
                            </div>
                        </div>
                        <p class="text-sm text-slate-500 leading-relaxed">Deteksi badai, curah hujan tinggi, dan anomali iklim laut secara instan pada rute pelayaran internasional.</p>
                        <div class="mt-4 flex items-center justify-between border-t border-border pt-3">
                            <span class="text-xs font-bold text-muted-foreground">Status Pelayaran</span>
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-teal-500/10 text-teal-600 border border-teal-500/25">NORMAL</span>
                        </div>
                    </div>

                    <!-- Currency / Volatility Card -->
                    <div class="skeuo-card p-6 text-left ml-6 lg:ml-10 transform rotate-1 hover:rotate-0 hover:scale-[1.02] transition-all duration-300 shadow-2xl bg-white/70 backdrop-blur-md border border-white/40">
                        <div class="flex items-center gap-3.5 mb-3">
                            <div class="bg-success/10 p-3 rounded-2xl">
                                <i class="fa-solid fa-wallet text-success text-2xl"></i>
                            </div>
                            <div>
                                <h6 class="font-bold text-base text-[#0f172a]">Volatilitas Kurs Valas</h6>
                                <small class="text-muted-foreground font-semibold">ExchangeRate API</small>
                            </div>
                        </div>
                        <p class="text-sm text-slate-500 leading-relaxed mb-3">Mengukur koefisien variasi mata uang lokal terhadap USD dalam 30 hari untuk mitigasi risiko.</p>
                        
                        <!-- Animated Sparkline Chart Mockup -->
                        <div class="h-10 w-full bg-slate-50 rounded-lg p-1 border border-border flex items-end relative overflow-hidden">
                            <svg class="w-full h-full text-success" viewBox="0 0 100 30" preserveAspectRatio="none">
                                <path d="M0,25 Q15,10 30,20 T60,5 T90,22 L100,10" fill="none" stroke="currentColor" stroke-width="2" />
                            </svg>
                            <span class="absolute right-2 top-2 text-[9px] font-mono font-bold text-success bg-success/10 px-1 rounded">LIVE</span>
                        </div>

                        <div class="mt-4 flex items-center justify-between border-t border-border pt-3">
                            <span class="text-xs font-bold text-muted-foreground">Volatilitas EUR/USD</span>
                            <span class="font-mono text-xs font-bold text-success"><i class="fa-solid fa-chart-line"></i> 1.25%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Features Showcase Section -->
<div class="bg-slate-50 border-t border-b border-border py-16 lg:py-24">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
        <div class="text-center max-w-2xl mx-auto mb-16 space-y-3">
            <span class="text-primary font-bold text-xs uppercase tracking-widest block">{{ __('FITUR UTAMA PLATFORM') }}</span>
            <h2 class="text-3xl font-extrabold text-[#0f172a]">{{ __('Analisis Risiko Rantai Pasok Terpadu') }}</h2>
            <p class="text-slate-500 text-sm md:text-base">{{ __('Empat pilar data utama diintegrasikan secara real-time untuk menghasilkan keputusan mitigasi logistik yang presisi.') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Feature 1 -->
            <div class="skeuo-card p-6 bg-white hover:scale-[1.02] transition-transform duration-300 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-12 h-12 rounded-xl bg-teal-500/10 border border-teal-500/25 flex items-center justify-center text-teal-600">
                        <i class="fa-solid fa-cloud-showers-water text-xl"></i>
                    </div>
                    <h4 class="font-bold text-lg text-[#0f172a]">{{ __('Klimatologi & Cuaca') }}</h4>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        {{ __('Deteksi otomatis cuaca ekstrem di koordinat pelabuhan utama untuk mengantisipasi keterlambatan kontainer secara instan.') }}
                    </p>
                </div>
            </div>

            <!-- Feature 2 -->
            <div class="skeuo-card p-6 bg-white hover:scale-[1.02] transition-transform duration-300 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/25 flex items-center justify-center text-amber-600">
                        <i class="fa-solid fa-coins text-xl"></i>
                    </div>
                    <h4 class="font-bold text-lg text-[#0f172a]">{{ __('Volatilitas Valas') }}</h4>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        {{ __('Pantau fluktuasi nilai tukar mata uang asing secara harian guna menghitung potensi kenaikan biaya pengiriman internasional.') }}
                    </p>
                </div>
            </div>

            <!-- Feature 3 -->
            <div class="skeuo-card p-6 bg-white hover:scale-[1.02] transition-transform duration-300 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-12 h-12 rounded-xl bg-sky-500/10 border border-sky-500/25 flex items-center justify-center text-sky-600">
                        <i class="fa-solid fa-newspaper text-xl"></i>
                    </div>
                    <h4 class="font-bold text-lg text-[#0f172a]">{{ __('Sentimen Berita') }}</h4>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        {{ __('Gunakan NLP (Natural Language Processing) untuk mengekstrak sentimen geopolitik dan ancaman logistik dari ribuan portal berita.') }}
                    </p>
                </div>
            </div>

            <!-- Feature 4 -->
            <div class="skeuo-card p-6 bg-white hover:scale-[1.02] transition-transform duration-300 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-12 h-12 rounded-xl bg-primary/10 border border-primary/25 flex items-center justify-center text-primary">
                        <i class="fa-solid fa-calculator text-xl"></i>
                    </div>
                    <h4 class="font-bold text-lg text-[#0f172a]">{{ __('Skor Risiko Komposit') }}</h4>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        {{ __('Formula pembobotan risiko dinamis berdasarkan bobot kustom yang menghasilkan indeks tingkat kerawanan suatu wilayah.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Popular Countries Grid Section -->
<div class="bg-white py-16 lg:py-24 border-b border-border">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
        <div class="text-center max-w-2xl mx-auto mb-16 space-y-3">
            <span class="text-primary font-bold text-xs uppercase tracking-widest block">{{ __('MONITORING WILAYAH') }}</span>
            <h2 class="text-3xl font-extrabold text-[#0f172a]">Ringkasan Risiko Negara Populer</h2>
            <p class="text-slate-500">Indeks risiko komposit terintegrasi yang diperbarui berdasarkan data cuaca, sentimen berita, inflasi, dan nilai tukar uang terbaru.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @forelse($popular as $country)
            <div class="skeuo-card p-6 flex flex-col justify-between hover:scale-[1.02] transition-all duration-300 bg-white">
                <div>
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <img src="{{ $country->flag_url }}" alt="Bendera {{ $country->name }}" width="45" class="rounded-xl shadow-sm border border-border">
                            <div>
                                <h5 class="font-bold text-base text-[#0f172a]">{{ $country->name }}</h5>
                                <span class="text-xs text-muted-foreground font-medium">{{ $country->capital }}</span>
                            </div>
                        </div>
                        <i class="fa-solid fa-earth-asia text-slate-300 text-xl"></i>
                    </div>

                    <div class="skeuo-inset p-4 my-6 text-center bg-slate-50">
                        <span class="text-muted-foreground text-xs font-bold uppercase tracking-wider block mb-1">Skor Risiko SCM</span>
                        @if($country->latestRiskScore)
                            <h2 class="text-4xl font-extrabold font-mono text-dark tabular-nums">{{ round($country->latestRiskScore->total_score) }}</h2>
                            <div class="mt-2">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-extrabold
                                    {{ $country->latestRiskScore->level === 'high' ? 'bg-red-500/10 text-red-600 border border-red-500/25' : ($country->latestRiskScore->level === 'medium' ? 'bg-amber-500/10 text-amber-600 border border-amber-500/25' : 'bg-teal-500/10 text-teal-600 border border-teal-500/25') }}">
                                    {{ strtoupper($country->latestRiskScore->level) }} RISK
                                </span>
                            </div>
                        @else
                            <h2 class="text-4xl font-extrabold text-muted-foreground/50">--</h2>
                            <div class="mt-2">
                                <span class="inline-flex px-3 py-1 rounded-full text-[10px] font-bold bg-muted text-muted-foreground">BELUM DIHITUNG</span>
                            </div>
                        @endif
                    </div>
                </div>

                <a href="{{ route('login') }}" class="btn-skeuo-outline w-full mt-2 hover:scale-[1.01] active:scale-95 transition-all">
                    Buka Analisis Detail
                </a>
            </div>
            @empty
            <div class="col-span-full text-center py-12 bg-surface rounded-2xl border border-border">
                <i class="fa-regular fa-folder-open text-muted-foreground text-4xl mb-3"></i>
                <p class="text-muted-foreground">Tidak ada data negara populer saat ini.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Premium Call-To-Action (CTA) Section -->
<div class="bg-slate-900 text-white py-16 lg:py-24 relative overflow-hidden">
    <!-- Grid overlay and glowing circle -->
    <div class="absolute inset-0 bg-[linear-gradient(rgba(20,184,166,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(20,184,166,0.03)_1px,transparent_1px)] bg-[size:30px_30px] pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8 relative z-10 space-y-6">
        <h2 class="text-3xl md:text-4xl font-extrabold leading-tight">
            {{ __('Mulai Mitigasi Risiko Rantai Pasok Anda Sekarang') }}
        </h2>
        <p class="text-slate-400 text-sm md:text-base max-w-2xl mx-auto leading-relaxed">
            {{ __('Dapatkan dasbor personalisasi risiko rantai pasok global secara instan. Amankan logistik pelayaran Anda dari guncangan geopolitik dan anomali iklim ekstrem.') }}
        </p>
        <div class="pt-4">
            <a href="{{ route('register') }}" class="btn-skeuo !bg-primary text-white !px-10 !py-4 hover:scale-[1.03] active:scale-95 transition-all inline-flex items-center gap-2">
                {{ __('Daftar Sekarang secara Gratis') }} <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>
@endsection
