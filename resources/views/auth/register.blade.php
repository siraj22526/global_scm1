@extends('layouts.guest')

@section('title', 'Daftar - Global SCM Risk Intel')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 min-h-screen w-full">
    <!-- Left Decorative Panel (5 Columns) with Cartoon Illustration inside Premium Glass Frame -->
    <div class="hidden lg:flex lg:col-span-5 flex-col justify-between p-12 text-[#0f172a] bg-gradient-to-br from-[#f0fdfa] via-[#ccfbf1] to-[#e0f2fe] relative overflow-hidden border-r border-border">
        <!-- Abstract gradient circles -->
        <div class="absolute top-[-10%] left-[-10%] w-80 h-80 bg-teal-200/25 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-72 h-72 bg-sky-200/35 rounded-full blur-3xl pointer-events-none"></div>
        <!-- Technical Grid Overlay -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(20,184,166,0.015)_1px,transparent_1px),linear-gradient(90deg,rgba(20,184,166,0.015)_1px,transparent_1px)] bg-[size:20px_20px] pointer-events-none z-0"></div>

        <!-- Top branding -->
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 z-10 hover:opacity-90 transition-opacity">
            <div class="bg-primary/10 p-2 rounded-xl border border-primary/20">
                <i class="fa-solid fa-earth-americas text-xl text-primary animate-[spin_10s_linear_infinite]"></i>
            </div>
            <span class="font-heading font-extrabold text-xl tracking-wide">GlobalSCM <span class="text-primary">Intel</span></span>
        </a>

        <!-- Middle Cartoon Illustration & Copy -->
        <div class="my-auto space-y-8 z-10 text-center animate-slide-up">
            <!-- Cartoon Image inside a Premium Glass Frame Mockup -->
            <div class="relative inline-block p-4 bg-white/45 backdrop-blur-md border border-white/60 rounded-3xl shadow-[0_20px_50px_rgba(15,118,110,0.18)] max-w-[310px] sm:max-w-[340px] mx-auto transform rotate-1 hover:rotate-0 hover:scale-[1.03] transition-all duration-500 cursor-pointer group">
                <!-- Floating shiny overlay -->
                <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700 rounded-3xl pointer-events-none"></div>
                <img src="{{ asset('images/register_illustration.png') }}" alt="Ilustrasi Registrasi Rantai Pasok" class="w-full h-auto rounded-2xl shadow-sm border border-white/50">
            </div>

            <div class="space-y-3 max-w-sm mx-auto text-left">
                <h2 class="text-2xl font-extrabold leading-tight text-[#0f172a]">Buat Akun Anda & Mulai Memantau</h2>
                <p class="text-[#475569] text-sm leading-relaxed">
                    Daftar akun hari ini untuk mempersonalisasi daftar pantauan Anda. Amankan rute rantai pasok Anda dari risiko iklim ekstrem dan guncangan ekonomi dunia.
                </p>
            </div>
        </div>

        <!-- Bottom footer -->
        <div class="text-xs text-slate-400 z-10 font-medium font-mono">
            &copy; {{ date('Y') }} GlobalSCM Intel.
        </div>
    </div>

    <!-- Right Form Panel (7 Columns) with Interactive Focus Glow -->
    <div class="lg:col-span-7 flex flex-col justify-center p-6 sm:p-10 lg:p-12 bg-[#f4f7fb]"
         x-data="{ nameFocused: false, emailFocused: false, passwordFocused: false, confirmFocused: false }">
        
        <!-- Centered Register Form Card -->
        <div class="w-full max-w-md mx-auto animate-slide-up my-auto">
            <!-- Card wrapper with dynamic border glow on focus -->
            <div class="skeuo-card p-8 sm:p-10 bg-white transition-all duration-300 relative overflow-hidden"
                 :class="{ 'ring-2 ring-primary/45 border-transparent shadow-[0_20px_40px_rgba(15,118,110,0.12)] scale-[1.01]': nameFocused || emailFocused || passwordFocused || confirmFocused }">
                
                <div class="text-center mb-8">
                    <div class="bg-primary/10 inline-flex p-4 rounded-full mb-3 border border-primary/20">
                        <i class="fa-solid fa-user-plus text-primary text-2xl animate-[bounce_3s_infinite]"></i>
                    </div>
                    <h3 class="font-bold text-2xl text-dark">Daftar Akun Baru</h3>
                    <p class="text-muted-foreground text-sm mt-1">Daftarkan diri Anda untuk memantau data risiko secara personal</p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf
                    <!-- Name Field -->
                    <div class="space-y-1.5">
                        <label for="name" class="block text-sm font-bold text-muted-foreground transition-colors duration-200"
                               :class="nameFocused ? 'text-primary' : ''">Nama Lengkap</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center transition-colors duration-200"
                                  :class="nameFocused ? 'text-primary' : 'text-muted-foreground/60'">
                                <i class="fa-solid fa-user"></i>
                            </span>
                            <input type="text" class="input-skeuo !pl-9 transition-all focus:scale-[1.01]" id="name" name="name" value="{{ old('name') }}" placeholder="Nama Lengkap Anda" required autofocus
                                   @focus="nameFocused = true" @blur="nameFocused = false">
                        </div>
                    </div>

                    <!-- Email Field -->
                    <div class="space-y-1.5">
                        <label for="email" class="block text-sm font-bold text-muted-foreground transition-colors duration-200"
                               :class="emailFocused ? 'text-primary' : ''">Alamat Email</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center transition-colors duration-200"
                                  :class="emailFocused ? 'text-primary' : 'text-muted-foreground/60'">
                                <i class="fa-solid fa-envelope"></i>
                            </span>
                            <input type="email" class="input-skeuo !pl-9 transition-all focus:scale-[1.01]" id="email" name="email" value="{{ old('email') }}" placeholder="email@example.com" required
                                   @focus="emailFocused = true" @blur="emailFocused = false">
                        </div>
                    </div>
                    
                    <!-- Password Field -->
                    <div class="space-y-1.5">
                        <label for="password" class="block text-sm font-bold text-muted-foreground transition-colors duration-200"
                               :class="passwordFocused ? 'text-primary' : ''">Password</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center transition-colors duration-200"
                                  :class="passwordFocused ? 'text-primary' : 'text-muted-foreground/60'">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input type="password" class="input-skeuo !pl-9 transition-all focus:scale-[1.01]" id="password" name="password" placeholder="Minimal 8 karakter" required
                                   @focus="passwordFocused = true" @blur="passwordFocused = false">
                        </div>
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="space-y-1.5">
                        <label for="password_confirmation" class="block text-sm font-bold text-muted-foreground transition-colors duration-200"
                               :class="confirmFocused ? 'text-primary' : ''">Konfirmasi Password</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center transition-colors duration-200"
                                  :class="confirmFocused ? 'text-primary' : 'text-muted-foreground/60'">
                                <i class="fa-solid fa-shield"></i>
                            </span>
                            <input type="password" class="input-skeuo !pl-9 transition-all focus:scale-[1.01]" id="password_confirmation" name="password_confirmation" placeholder="Ulangi password" required
                                   @focus="confirmFocused = true" @blur="confirmFocused = false">
                        </div>
                    </div>

                    <button type="submit" class="btn-skeuo w-full !min-h-12 !text-base mt-4 relative overflow-hidden group hover:scale-[1.02] hover:shadow-lg active:scale-98 active:shadow-sm transition-all duration-200">
                        <span class="absolute inset-0 w-full h-full bg-white/10 transform -translate-x-full group-hover:translate-x-0 transition-transform duration-300"></span>
                        <span class="relative z-10 flex items-center justify-center gap-2">
                            Daftar Sekarang <i class="fa-solid fa-arrow-right-to-bracket transition-transform group-hover:translate-x-1"></i>
                        </span>
                    </button>

                    <!-- Divider & Navigation Buttons integrated inside the form card -->
                    <div class="text-center pt-4 border-t border-border mt-5 space-y-4">
                        <p class="text-sm text-muted-foreground">
                            Sudah punya akun? 
                            <a href="{{ route('login') }}" class="text-primary font-bold hover:underline">
                                Masuk di Sini
                            </a>
                        </p>
                        
                        <div class="pt-2">
                            <a href="{{ route('home') }}" class="btn-skeuo-outline w-full flex items-center justify-center gap-2 !min-h-11 hover:scale-[1.02] hover:shadow-md active:scale-95 transition-all">
                                <i class="fa-solid fa-house"></i> Kembali ke Beranda
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Footer for Mobile View (Hidden on large screens) -->
        <div class="text-center text-xs text-muted-foreground lg:hidden pt-4">
            &copy; {{ date('Y') }} GlobalSCM Intel
        </div>
    </div>
</div>
@endsection
