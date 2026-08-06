<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'BMS Mobile')</title>
    
    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- FontAwesome Font Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        body { -webkit-tap-highlight-color: transparent; }
    </style>
</head>
<body class="h-full font-sans text-slate-800 antialiased flex flex-col max-w-md mx-auto shadow-2xl bg-slate-50 relative">

    <!-- ─── MAIN SCROLL CONTAINER ─── -->
    <main class="flex-1 overflow-y-auto no-scrollbar pb-6 relative">
        @yield('content')
    </main>

    <!-- ─── INCLUDE PARTIALS FOR NAVIGATION & DRAWER ─── -->
    @include('partials.mobile-buttom')

    <!-- ─── JAVASCRIPT GLOBAL (LIVE TIME & GPS) ─── -->
    <script>
        // Live Clock Widget
        function updateClock() {
            const clockEl = document.getElementById('live-clock');
            if (!clockEl) return;
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            clockEl.textContent = `${hours}:${minutes}:${seconds}`;
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Live Real-Time Geolocation GPS Tracker
        const gpsEl = document.getElementById('gps-status');
        if (gpsEl) {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude.toFixed(4);
                        const lon = position.coords.longitude.toFixed(4);
                        gpsEl.innerHTML = `<i class="fa-solid fa-location-dot text-emerald-500 mr-1"></i> ${lat}, ${lon}`;
                    },
                    (error) => {
                        // Fallback Koordinat Default Jakarta/Kantor Pusat jika GPS Ditolak
                        gpsEl.innerHTML = `<i class="fa-solid fa-location-dot text-amber-500 mr-1"></i> Terkunci (HQ)`;
                    }
                );
            } else {
                gpsEl.textContent = "Tidak Didukung";
            }
        }

        // Global Sidebar Menu Toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            const backdrop = document.getElementById('menu-backdrop');
            
            if (menu.classList.contains('-translate-x-full')) {
                menu.classList.remove('-translate-x-full');
                backdrop.classList.remove('opacity-0', 'pointer-events-none');
            } else {
                menu.classList.add('-translate-x-full');
                backdrop.classList.add('opacity-0', 'pointer-events-none');
            }
        }
    </script>
</body>
</html>