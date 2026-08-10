<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Admin - AssetKu')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Tailwind & Alpine JS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#3b82f6',
                            dark: '#2563eb',
                        }
                    }
                }
            }
        }

        // Fast theme init to prevent flickering
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    {{-- Fonts & Lucide Icons --}}
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/x-icon">
    <script src="https://unpkg.com/lucide@latest"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* Glassmorphism Premium */
        .glass-layout {
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
        }

        /* CUSTOM SCROLLBAR */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.3);
            border-radius: 9999px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(59, 130, 246, 0.6);
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* ==========================================
       CHOICES.JS STYLING (LIGHT & DARK MODE FIX)
       ========================================== */

        /* Kotak Utama Input Select */
        .choices__inner {
            background-color: rgb(248 250 252) !important;
            /* slate-50 */
            border: 1px solid rgb(226 232 240) !important;
            /* border-slate-200 */
            border-radius: 0.75rem !important;
            /* rounded-xl */
            color: rgb(51 65 85) !important;
            /* slate-700 */
            min-height: 42px;
            padding: 4px 12px;
        }

        .dark .choices__inner {
            background-color: rgb(15 23 42) !important;
            /* slate-900 */
            border-color: rgb(51 65 85) !important;
            /* slate-700 */
            color: rgb(226 232 240) !important;
            /* slate-200 */
        }

        /* Kotak Dropdown Container */
        .choices__list--dropdown {
            background-color: white !important;
            border: 1px solid rgb(226 232 240) !important;
            border-radius: 0.75rem !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
            margin-top: 4px;
            overflow: hidden;
        }

        .dark .choices__list--dropdown {
            background-color: rgb(15 23 42) !important;
            /* slate-900 */
            border-color: rgb(51 65 85) !important;
            /* slate-700 */
        }

        /* Input Pencarian di dalam Dropdown */
        .choices__input {
            background-color: rgb(241 245 249) !important;
            /* slate-100 */
            border: 1px solid rgb(226 232 240) !important;
            border-radius: 0.5rem !important;
            color: rgb(51 65 85) !important;
            margin-bottom: 8px !important;
        }

        .dark .choices__input {
            background-color: rgb(30 41 59) !important;
            /* slate-800 */
            border-color: rgb(51 65 85) !important;
            /* slate-700 */
            color: rgb(241 245 249) !important;
            /* slate-100 */
        }

        /* Item Opsi Default */
        .choices__list--dropdown .choices__item {
            background-color: transparent !important;
            color: rgb(51 65 85) !important;
            /* slate-700 */
            padding: 10px 14px !important;
            font-size: 0.875rem;
        }

        .dark .choices__list--dropdown .choices__item {
            color: rgb(203 213 225) !important;
            /* slate-300 */
        }

        /* State: Saat Mouse Menyorot (Hover / Highlighted) */
        .choices__list--dropdown .choices__item--selectable.is-highlighted {
            background-color: rgb(59 130 246) !important;
            /* blue-500 */
            color: white !important;
        }

        /* State: Saat Item Terpilih / Data Old (Selected) */
        .choices__list--dropdown .choices__item--selectable.is-selected {
            background-color: rgb(241 245 249) !important;
            /* slate-100 */
            color: rgb(30 41 59) !important;
            /* slate-800 */
            font-weight: 600;
        }

        .dark .choices__list--dropdown .choices__item--selectable.is-selected {
            background-color: rgb(30 41 59) !important;
            /* slate-800 */
            color: rgb(241 245 249) !important;
            /* slate-100 */
        }

        /* Icon panah select */
        .choices[data-type*="select-one"]::after {
            border-color: rgb(148 163 184) transparent transparent transparent !important;
            right: 16px;
        }
    </style>
</head>

<body x-data="{ sidebarOpen: false, sidebarCollapsed: false }"
    class="bg-slate-100 dark:bg-[#070b14] text-slate-800 dark:text-slate-100 antialiased transition-colors duration-300 relative min-h-screen overflow-hidden">

    <!-- BACKGROUND AURA BLOBS -->
    <div class="fixed top-[-15%] left-[-10%] w-[55vw] h-[55vh] rounded-full bg-gradient-to-tr from-blue-500/20 to-indigo-500/10 dark:from-blue-600/10 dark:to-indigo-800/5 blur-[140px] pointer-events-none z-0"></div>
    <div class="fixed bottom-[-15%] right-[-10%] w-[60vw] h-[60vh] rounded-full bg-gradient-to-br from-indigo-400/10 to-purple-500/15 dark:from-purple-900/10 dark:to-transparent blur-[160px] pointer-events-none z-0"></div>

    <div class="flex h-screen overflow-hidden relative z-10">

        {{-- SIDEBAR INCLUSION --}}
        @include('partials.admin-sidebar')

        {{-- MAIN WRAPPER (Dynamically adjusts margin based on desktop sidebar state) --}}
        <div :class="{
                 'lg:pl-64': !sidebarCollapsed,
                 'lg:pl-20': sidebarCollapsed
             }"
            class="flex-1 flex flex-col min-w-0 h-full transition-all duration-300 ease-in-out z-10">

            {{-- HEADER --}}
            @include('partials.admin-header')

            {{-- CONTENT AREA --}}
            <main class="flex-1 overflow-hidden p-3 md:p-5 flex flex-col min-h-0 relative">

                {{-- WATERMARK GLOBAL --}}
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-0 select-none overflow-hidden opacity-[0.02] dark:opacity-[0.03]">
                    <div class="text-center transform -rotate-12">
                        <p class="text-8xl md:text-9xl font-black uppercase tracking-widest text-slate-900 dark:text-white">
                            ASSETKU
                        </p>
                        <p class="text-xs md:text-base font-bold tracking-[0.4em] text-slate-700 dark:text-slate-300 mt-2">
                            INTERNAL ADMIN SYSTEM
                        </p>
                    </div>
                </div>

                <!-- Main Content Container -->
                <div class="flex-1 bg-white/70 dark:bg-slate-900/60 backdrop-blur-xl rounded-2xl border border-slate-200/80 dark:border-slate-800/80 overflow-hidden shadow-sm flex flex-col relative z-10">

                    <div class="flex-1 overflow-y-auto p-4 md:p-6 custom-scrollbar">
                        <div class="max-w-[1600px] mx-auto space-y-5">

                            {{-- GLOBAL ALERTS --}}

                            {{-- Error Alert --}}
                            @if(session('error'))
                            <div x-data="{ show: true }" x-show="show" x-transition
                                class="p-4 rounded-xl bg-rose-500/10 border-l-4 border-rose-500 text-rose-700 dark:text-rose-400 flex items-start justify-between gap-3 shadow-sm backdrop-blur-md">
                                <div class="flex items-start gap-3">
                                    <i data-lucide="alert-triangle" class="w-5 h-5 shrink-0 mt-0.5 text-rose-500"></i>
                                    <div>
                                        <h4 class="text-sm font-bold">Terjadi Kesalahan</h4>
                                        <p class="text-xs mt-0.5 opacity-90">{{ session('error') }}</p>
                                    </div>
                                </div>
                                <button @click="show = false" type="button" class="text-rose-500 hover:text-rose-700 dark:hover:text-rose-300 transition-colors">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            </div>
                            @endif

                            {{-- Validation Errors Alert --}}
                            @if ($errors->any())
                            <div x-data="{ show: true }" x-show="show" x-transition
                                class="p-4 rounded-xl bg-amber-500/10 border-l-4 border-amber-500 text-amber-800 dark:text-amber-300 flex items-start justify-between gap-3 shadow-sm backdrop-blur-md">
                                <div class="flex items-start gap-3">
                                    <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 mt-0.5 text-amber-500"></i>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-bold">Periksa Kembali Inputan Anda:</h4>
                                        <ul class="list-disc list-inside text-xs mt-1.5 space-y-1 opacity-90">
                                            @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                <button @click="show = false" type="button" class="text-amber-600 hover:text-amber-800 dark:hover:text-amber-200 transition-colors">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            </div>
                            @endif

                            {{-- Success Alert --}}
                            @if(session('success'))
                            <div x-data="{ show: true }" x-show="show" x-transition
                                class="p-4 rounded-xl bg-emerald-500/10 border-l-4 border-emerald-500 text-emerald-800 dark:text-emerald-300 flex items-start justify-between gap-3 shadow-sm backdrop-blur-md">
                                <div class="flex items-start gap-3">
                                    <i data-lucide="check-circle-2" class="w-5 h-5 shrink-0 mt-0.5 text-emerald-500"></i>
                                    <div>
                                        <h4 class="text-sm font-bold">Berhasil!</h4>
                                        <p class="text-xs mt-0.5 opacity-90">{{ session('success') }}</p>
                                    </div>
                                </div>
                                <button @click="show = false" type="button" class="text-emerald-600 hover:text-emerald-800 dark:hover:text-emerald-200 transition-colors">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            </div>
                            @endif

                            {{-- MAIN PAGE CONTENT --}}
                            @yield('content')

                        </div>
                    </div>

                </div>
            </main>

        </div>
    </div>

    <script>
        lucide.createIcons();

        // Dark Mode Toggle Sync
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('dark-mode-toggle');
            const sunIcon = document.getElementById('sun-icon');
            const moonIcon = document.getElementById('moon-icon');
            const html = document.documentElement;

            function syncThemeIcons() {
                if (html.classList.contains('dark')) {
                    if (sunIcon) sunIcon.classList.remove('hidden');
                    if (moonIcon) moonIcon.classList.add('hidden');
                } else {
                    if (sunIcon) sunIcon.classList.add('hidden');
                    if (moonIcon) moonIcon.classList.remove('hidden');
                }
            }

            syncThemeIcons();

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    if (html.classList.contains('dark')) {
                        html.classList.remove('dark');
                        localStorage.setItem('theme', 'light');
                    } else {
                        html.classList.add('dark');
                        localStorage.setItem('theme', 'dark');
                    }
                    syncThemeIcons();
                });
            }
        });
    </script>
</body>

</html>