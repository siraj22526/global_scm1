<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Global Supply Chain Risk Platform')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @yield('styles')
</head>
<body class="bg-background text-foreground font-sans min-h-screen flex flex-col" x-data="{ mobileMenuOpen: false }">

    @if(!request()->routeIs('login') && !request()->routeIs('register'))
    <header class="sticky top-0 z-30 bg-white/90 backdrop-blur-md border-b border-border">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <i class="fa-solid fa-earth-americas text-primary text-xl"></i>
                <span class="font-heading font-bold tracking-wide">GlobalSCM <span class="text-primary">Intel</span></span>
            </a>

            <nav class="hidden md:flex items-center gap-3">
                <a href="{{ route('login') }}" class="btn-skeuo-outline">Masuk</a>
                <a href="{{ route('register') }}" class="btn-skeuo">Daftar</a>
            </nav>

            <button @click="mobileMenuOpen = !mobileMenuOpen" aria-label="Buka menu"
                    class="md:hidden flex items-center justify-center w-11 h-11 rounded-xl hover:bg-muted">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>
        </div>

        <div x-show="mobileMenuOpen" x-cloak x-transition class="md:hidden border-t border-border bg-white px-4 py-4 flex flex-col gap-3">
            <a href="{{ route('login') }}" class="btn-skeuo-outline w-full">Masuk</a>
            <a href="{{ route('register') }}" class="btn-skeuo w-full">Daftar</a>
        </div>
    </header>
    @endif

    <main class="flex-1 flex flex-col">
        @yield('content')
    </main>

    @if(!request()->routeIs('login') && !request()->routeIs('register'))
    <footer class="border-t border-slate-800 bg-[#0b1329] text-slate-400 py-16">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
                <!-- Branding Column -->
                <div class="space-y-4 text-left">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 text-white">
                        <i class="fa-solid fa-earth-americas text-primary text-xl"></i>
                        <span class="font-heading font-bold tracking-wide">GlobalSCM <span class="text-primary">Intel</span></span>
                    </a>
                    <p class="text-xs leading-relaxed text-slate-400">
                        Platform intelijen risiko rantai pasok global berbasis Decision Support System untuk mitigasi risiko iklim, inflasi, valas, dan geopolitik secara real-time.
                    </p>
                    <div class="flex items-center gap-3 pt-2">
                        <a href="#" class="w-8 h-8 rounded-xl bg-slate-800 hover:bg-primary hover:text-white flex items-center justify-center transition-colors text-xs text-slate-400"><i class="fa-brands fa-x-twitter"></i></a>
                        <a href="#" class="w-8 h-8 rounded-xl bg-slate-800 hover:bg-primary hover:text-white flex items-center justify-center transition-colors text-xs text-slate-400"><i class="fa-brands fa-linkedin-in"></i></a>
                        <a href="#" class="w-8 h-8 rounded-xl bg-slate-800 hover:bg-primary hover:text-white flex items-center justify-center transition-colors text-xs text-slate-400"><i class="fa-brands fa-github"></i></a>
                    </div>
                </div>

                <!-- Column 2: Fitur Utama -->
                <div class="space-y-3 text-left">
                    <h5 class="text-white font-bold text-sm tracking-wider uppercase">Fitur Analitik</h5>
                    <ul class="space-y-2 text-xs">
                        <li><a href="#" class="hover:text-white transition-colors">Dasbor Utama</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Pemantauan Cuaca Ekstrem</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Volatilitas Valuta Asing</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Analisis Sentimen Berita</a></li>
                    </ul>
                </div>

                <!-- Column 3: Navigasi Cepat -->
                <div class="space-y-3 text-left">
                    <h5 class="text-white font-bold text-sm tracking-wider uppercase">Akses Cepat</h5>
                    <ul class="space-y-2 text-xs">
                        <li><a href="{{ route('login') }}" class="hover:text-white transition-colors">Masuk Platform</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-white transition-colors">Daftar Akun</a></li>
                        <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">Halaman Beranda</a></li>
                    </ul>
                </div>

                <!-- Column 4: Kontak & Keamanan -->
                <div class="space-y-3 text-left">
                    <h5 class="text-white font-bold text-sm tracking-wider uppercase">Dukungan</h5>
                    <ul class="space-y-2 text-xs">
                        <li><span class="block">Email: support@globalscm.id</span></li>
                        <li><span class="block">Pusat Bantuan: docs.globalscm.id</span></li>
                        <li><span class="block">Status Server: <span class="text-emerald-400 font-bold"><i class="fa-solid fa-circle text-[8px] animate-pulse"></i> Normal</span></span></li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Section -->
            <div class="border-t border-slate-800 pt-8 flex flex-col md:flex-row items-center justify-between gap-4 text-xs">
                <div>
                    &copy; {{ date('Y') }} GlobalSCM Intel. Hak Cipta Dilindungi Undang-Undang.
                </div>
                <div class="flex items-center gap-6">
                    <a href="#" class="hover:text-white transition-colors">Ketentuan Layanan</a>
                    <a href="#" class="hover:text-white transition-colors">Kebijakan Privasi</a>
                </div>
            </div>
        </div>
    </footer>
    @endif

    <div id="toastRoot" class="fixed bottom-4 right-4 z-[100] flex flex-col gap-2 items-end"></div>

    @yield('scripts')

    @if($errors->any())
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    title: 'Kesalahan Validasi',
                    text: "{{ $errors->first() }}",
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
            });
        </script>
    @endif

    @if(session('logout_success'))
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    title: 'Keluar Berhasil',
                    text: "{{ session('logout_success') }}",
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
            });
        </script>
    @endif
</body>
</html>
