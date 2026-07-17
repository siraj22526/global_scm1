<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Global Supply Chain Risk Platform')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- FontAwesome Icons -->
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" rel="stylesheet">

    <!-- Leaflet.js CSS (Maps) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" />

    <!-- Leaflet MarkerCluster CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- App Styles / Scripts (Tailwind CSS 4 + Alpine.js via Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @yield('styles')
</head>
<body class="bg-background text-foreground font-sans" x-data="{ drawerOpen: false }">

    @php
        $navLinks = [
            ['route' => 'dashboard', 'path' => 'dashboard', 'icon' => 'fa-chart-column', 'label' => 'Dashboard'],
            ['url' => '/weather', 'path' => 'weather', 'icon' => 'fa-cloud-sun-rain', 'label' => 'Cuaca'],
            ['url' => '/currency', 'path' => 'currency', 'icon' => 'fa-money-bill-transfer', 'label' => 'Valuta'],
            ['url' => '/ports', 'path' => 'ports', 'icon' => 'fa-ship', 'label' => 'Pelabuhan'],
            ['url' => '/news', 'path' => 'news', 'icon' => 'fa-newspaper', 'label' => 'Berita'],
            ['url' => '/analytics', 'path' => 'analytics', 'icon' => 'fa-chart-line', 'label' => 'Analitik'],
            ['url' => '/compare', 'path' => 'compare', 'icon' => 'fa-scale-balanced', 'label' => 'Komparasi'],
            ['route' => 'watchlist', 'path' => 'watchlist', 'icon' => 'fa-star', 'label' => 'Watchlist'],
        ];
    @endphp

    <!-- Desktop sidebar (>=1024px) -->
    <aside class="hidden lg:flex lg:flex-col fixed inset-y-0 left-0 w-64 bg-white border-r border-border z-30">
        <a href="{{ route('home') }}" class="flex items-center gap-2 px-6 h-16 border-b border-border shrink-0">
            <i class="fa-solid fa-earth-americas text-primary text-xl"></i>
            <span class="font-heading font-bold tracking-wide">GlobalSCM <span class="text-primary">Intel</span></span>
        </a>

        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            @foreach($navLinks as $link)
                @php $active = Request::is($link['path']); @endphp
                <a href="{{ isset($link['route']) ? route($link['route']) : $link['url'] }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors duration-150
                          {{ $active ? 'bg-primary/10 text-primary border-l-2 border-primary' : 'text-muted-foreground hover:text-foreground hover:bg-muted border-l-2 border-transparent' }}">
                    <i class="fa-solid {{ $link['icon'] }} w-4 text-center {{ $link['icon'] === 'fa-star' ? 'text-warning' : '' }}"></i>
                    <span>{{ $link['label'] }}</span>
                </a>
            @endforeach

            @if(Auth::user()->role === 'admin')
                <a href="{{ route('admin') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors duration-150
                          {{ Request::is('admin') ? 'bg-primary/10 text-primary border-l-2 border-primary' : 'text-muted-foreground hover:text-foreground hover:bg-muted border-l-2 border-transparent' }}">
                    <i class="fa-solid fa-user-shield w-4 text-center"></i>
                    <span>Admin</span>
                </a>
            @endif
        </nav>



        <div class="p-3 border-t border-border shrink-0">
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.outside="open = false"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-muted transition-colors duration-150 min-h-11">
                    <i class="fa-solid fa-user-circle text-primary text-xl"></i>
                    <div class="flex-1 text-left overflow-hidden">
                        <p class="text-sm font-semibold truncate">{{ Auth::user()->name }}</p>
                        <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold {{ Auth::user()->role === 'admin' ? 'bg-danger text-white' : 'bg-primary text-white' }}">{{ strtoupper(Auth::user()->role) }}</span>
                    </div>
                    <i class="fa-solid fa-chevron-up text-xs text-muted-foreground transition-transform" :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open" x-cloak x-transition.origin.bottom
                     class="absolute bottom-full left-0 right-0 mb-2 skeuo-card p-1">
                    <form action="{{ route('logout') }}" method="POST" class="logout-form">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-3 py-2.5 rounded-lg text-danger hover:bg-danger/10 text-sm font-medium min-h-11">
                            <i class="fa-solid fa-right-from-bracket"></i> Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <!-- Mobile top bar (<1024px) -->
    <header class="lg:hidden fixed top-0 inset-x-0 h-16 bg-white/95 backdrop-blur-md border-b border-border z-30 flex items-center justify-between px-4">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <i class="fa-solid fa-earth-americas text-primary text-lg"></i>
            <span class="font-heading font-bold text-sm">GlobalSCM <span class="text-primary">Intel</span></span>
        </a>
        <button @click="drawerOpen = true" aria-label="Buka menu navigasi"
                class="flex items-center justify-center w-11 h-11 rounded-xl hover:bg-muted text-foreground">
            <i class="fa-solid fa-bars text-lg"></i>
        </button>
    </header>

    <!-- Mobile drawer -->
    <div x-show="drawerOpen" x-cloak x-transition.opacity class="lg:hidden fixed inset-0 bg-black/50 z-40" @click="drawerOpen = false"></div>
    <div x-show="drawerOpen" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         class="lg:hidden fixed inset-y-0 left-0 w-[85%] max-w-sm bg-white z-50 flex flex-col overflow-y-auto">
        <div class="flex items-center justify-between h-16 px-4 border-b border-border shrink-0">
            <span class="font-heading font-bold">Menu Navigasi</span>
            <button @click="drawerOpen = false" aria-label="Tutup menu" class="w-11 h-11 flex items-center justify-center rounded-xl hover:bg-muted">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <nav class="flex-1 py-4 px-3 space-y-1">
            @foreach($navLinks as $link)
                @php $active = Request::is($link['path']); @endphp
                <a href="{{ isset($link['route']) ? route($link['route']) : $link['url'] }}"
                   class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium min-h-11
                          {{ $active ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:text-foreground hover:bg-muted' }}">
                    <i class="fa-solid {{ $link['icon'] }} w-4 text-center {{ $link['icon'] === 'fa-star' ? 'text-warning' : '' }}"></i>
                    <span>{{ $link['label'] }}</span>
                </a>
            @endforeach

            @if(Auth::user()->role === 'admin')
                <a href="{{ route('admin') }}"
                   class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium min-h-11
                          {{ Request::is('admin') ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:text-foreground hover:bg-muted' }}">
                    <i class="fa-solid fa-user-shield w-4 text-center"></i>
                    <span>Admin</span>
                </a>
            @endif
        </nav>


            <div class="flex items-center gap-3 px-3 py-2 mb-2">
                <i class="fa-solid fa-user-circle text-primary text-xl"></i>
                <div class="flex-1 overflow-hidden">
                    <p class="text-sm font-semibold truncate">{{ Auth::user()->name }}</p>
                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold {{ Auth::user()->role === 'admin' ? 'bg-danger text-white' : 'bg-primary text-white' }}">{{ strtoupper(Auth::user()->role) }}</span>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="logout-form">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2 px-3 py-3 rounded-xl text-danger hover:bg-danger/10 text-sm font-medium min-h-11">
                    <i class="fa-solid fa-right-from-bracket"></i> Keluar
                </button>
            </form>
        </div>
    </div>

    <!-- Page Content -->
    <main class="lg:pl-64 pt-16 lg:pt-0 min-h-screen">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-8 animate-slide-up">
            @yield('content')
        </div>
    </main>

    <!-- Toast Notification Root -->
    <div id="toastRoot" class="fixed bottom-4 right-4 z-[100] flex flex-col gap-2 items-end"></div>

    <!-- Leaflet / Chart.js (libraries retained as-is per requirements) -->
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

    @yield('scripts')

    <!-- Logout confirmation and login/register success handlers -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Intercept all forms with class logout-form
            document.querySelectorAll('.logout-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Konfirmasi Keluar',
                        text: 'Apakah Anda yakin ingin keluar dari aplikasi?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Keluar',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#64748b',
                        background: '#ffffff',
                        showClass: { popup: 'animate__animated animate__zoomIn animate__faster' },
                        hideClass: { popup: 'animate__animated animate__fadeOut animate__faster' },
                        customClass: {
                            popup: 'rounded-2xl border border-border shadow-2xl',
                            confirmButton: 'btn-skeuo !bg-none !bg-[var(--color-danger)] !shadow-none',
                            cancelButton: 'btn-skeuo-outline !shadow-none'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            // 2. Display success alerts for login & register
            @if(session('login_success'))
                Swal.fire({
                    title: 'Login Berhasil!',
                    text: "{{ session('login_success') }}",
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
            @endif

            @if(session('register_success'))
                Swal.fire({
                    title: 'Pendaftaran Berhasil!',
                    text: "{{ session('register_success') }}",
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
            @endif
        });
    </script>
</body>
</html>
