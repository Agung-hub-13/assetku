<x-guest-layout>
    <!-- BACKGROUND UTAMA: Gambar Tech Premium + Lapisan Mesh Gradasi Dinamis -->
    <div class="fixed inset-0 z-0 overflow-hidden pointer-events-none">
        <img src="https://images.unsplash.com/photo-1557683316-973673baf926?q=80&w=2000&auto=format&fit=crop" 
             class="w-full h-full object-cover opacity-10 md:opacity-20 filter saturate-150 transform scale-105" alt="Abstract Data BG">
        
        <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-indigo-950/90 to-slate-950 mix-blend-multiply"></div>
        
        <div class="absolute top-[-10%] left-[-10%] w-[60vw] h-[60vw] bg-blue-500/10 md:bg-blue-500/20 rounded-full blur-[130px] animate-pulse" style="animation-duration: 8s;"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[60vw] h-[60vw] bg-indigo-500/10 md:bg-indigo-500/20 rounded-full blur-[130px] animate-pulse" style="animation-duration: 12s;"></div>
        
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff02_1px,transparent_1px),linear-gradient(to_bottom,#ffffff02_1px,transparent_1px)] bg-[size:3rem_3rem]"></div>
    </div>

    <!-- MAIN CONTAINER -->
    <div class="min-h-screen flex items-center justify-center p-3 sm:p-6 md:p-8 lg:p-12 relative z-10">
        <div class="w-full max-w-5xl bg-slate-900/40 backdrop-blur-2xl rounded-3xl md:rounded-[2.5rem] shadow-[0_25px_70px_-15px_rgba(0,0,0,0.8)] border border-white/10 flex flex-col md:flex-row overflow-hidden">
            
            <!-- KIRI: Visual Branding Panel (Premium Desktop View) -->
            <div class="hidden md:flex relative w-1/2 p-12 lg:p-16 flex-col justify-between overflow-hidden group border-r border-white/5">
                <div class="absolute inset-0 z-0">
                    <img src="https://images.unsplash.com/photo-1639322537228-f710d846310a?q=80&w=2000&auto=format&fit=crop" 
                         class="w-full h-full object-cover opacity-25 group-hover:opacity-35 group-hover:scale-105 transition-all duration-1000 ease-out" alt="Tech Assets">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-slate-950/20"></div>
                </div>

                <div class="relative z-10 flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-white/5 backdrop-blur-xl border border-white/10 shadow-lg shadow-black/20">
                        <x-application-logo class="w-6 h-6 text-blue-400 fill-current drop-shadow-[0_0_8px_rgba(96,165,250,0.5)]" />
                    </div>
                    <div class="flex flex-col">
                        <span class="text-white font-black tracking-widest text-[11px] uppercase bg-gradient-to-r from-blue-400 to-indigo-400 bg-clip-text text-transparent">Assetku</span>
                    </div>
                </div>

                <div class="relative z-10 space-y-6 pt-32">
                    <div class="space-y-4">
                        <h1 class="text-3xl lg:text-4xl xl:text-5xl font-black text-white tracking-tight leading-[1.15]">
                            Kecerdasan <br>Operasional <span class="bg-gradient-to-r from-blue-400 via-cyan-400 to-indigo-400 bg-clip-text text-transparent">Satu Pintu.</span>
                        </h1>
                        <p class="text-slate-400 text-sm xl:text-base max-w-sm font-light leading-relaxed">
                            Kelola aset infrastruktur, monitoring fasilitas, dan analisis data analitik tingkat tinggi dalam satu platform pintar terenkripsi.
                        </p>
                    </div>
                    
                    <div class="pt-6 border-t border-white/5 flex items-center justify-between text-[10px] text-slate-500 tracking-wider font-medium">
                        <span>PT SENTRAL LAYANAN PRIMA</span>
                        <span>&copy; {{ date('Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- KANAN: Form Login -->
            <div class="w-full md:w-1/2 flex flex-col justify-center p-6 sm:p-12 lg:p-16 bg-slate-950/30 md:bg-transparent relative">
                <div class="w-full max-w-md mx-auto space-y-8 relative z-10">
                    
                    <div class="space-y-2">
                        <div class="md:hidden flex items-center gap-3 mb-6 bg-white/5 p-3 rounded-2xl border border-white/5">
                            <div class="p-2 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/20">
                                <x-application-logo class="w-5 h-5 text-white fill-current" />
                            </div>
                            <div class="flex flex-col">
                                <span class="text-white font-black tracking-wider text-xs uppercase bg-gradient-to-r from-blue-400 to-indigo-400 bg-clip-text text-transparent">Assetku</span>
                                <span class="text-[9px] text-slate-400 font-medium">PT Sentral Layanan Prima</span>
                            </div>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Selamat Datang di SLP</h2>
                        <p class="text-xs sm:text-sm text-slate-400 font-medium">Silakan akses akun korporat terverifikasi Anda.</p>
                    </div>

                    <!-- PENG FIX SINTAKS: Pesan Error Alert Masuk Di Sini -->
                    @if ($errors->any())
                        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs font-semibold space-y-1">
                            @foreach ($errors->all() as $error)
                                <p class="flex items-center gap-2">
                                    <i class="fa-solid fa-circle-exclamation text-[10px]"></i> {{ $error }}
                                </p>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf
                        
                        <!-- Input Email -->
                        <div class="space-y-2">
                            <label for="email" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block ml-1">Kredensial Email</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500 group-focus-within:text-blue-400 transition-colors duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"></path>
                                    </svg>
                                </div>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus 
                                    class="w-full pl-11 pr-4 py-3.5 bg-white/5 border border-white/10 rounded-2xl focus:bg-slate-900/80 focus:border-blue-500/80 focus:ring-4 focus:ring-blue-500/10 transition-all duration-300 text-white text-sm font-medium placeholder:text-slate-600" 
                                    placeholder="nama@company.com">
                            </div>
                        </div>

                        <!-- Input Password -->
                        <div class="space-y-2">
                            <div class="flex justify-between items-center px-1">
                                <label for="password" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Kata Sandi</label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="text-[10px] font-bold text-blue-400 hover:text-blue-300 transition-colors tracking-wide">Lupa Sandi?</a>
                                @endif
                            </div>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500 group-focus-within:text-blue-400 transition-colors duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <input id="password" type="password" name="password" required 
                                    class="w-full pl-11 pr-4 py-3.5 bg-white/5 border border-white/10 rounded-2xl focus:bg-slate-900/80 focus:border-blue-500/80 focus:ring-4 focus:ring-blue-500/10 transition-all duration-300 text-white text-sm font-medium placeholder:text-slate-600" 
                                    placeholder="••••••••••••">
                            </div>
                        </div>

                        <!-- Remember Me -->
                        <div class="flex items-center px-1 py-1">
                            <input id="remember_me" type="checkbox" name="remember" 
                                   class="w-4 h-4 text-blue-500 bg-slate-950 border-white/10 rounded focus:ring-blue-500/40 focus:ring-offset-slate-950 focus:ring-offset-2 bg-transparent transition-all">
                            <label for="remember_me" class="ml-2.5 text-xs font-semibold text-slate-400 select-none cursor-pointer hover:text-slate-300 transition-colors">Ingat perangkat ini</label>
                        </div>

                        <!-- Tombol Submit -->
                        <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-2xl font-bold text-sm shadow-[0_10px_25px_-5px_rgba(59,130,246,0.3)] hover:shadow-[0_15px_30px_-2px_rgba(59,130,246,0.5)] active:scale-[0.98] transition-all duration-300 flex items-center justify-center gap-2 group cursor-pointer">
                            <span>Masuk ke Dashboard</span>
                            <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </button>
                    </form>
                </div>
                
                <div class="md:hidden text-center mt-12 text-[10px] text-slate-600 tracking-wider font-semibold">
                    PT SENTRAL LAYANAN PRIMA &copy; {{ date('Y') }}
                </div>
            </div>

        </div>
    </div>
</x-guest-layout>