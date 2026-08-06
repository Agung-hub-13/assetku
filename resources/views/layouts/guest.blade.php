<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Professional Portal') }}</title>

    <!-- Google Fonts: Plus Jakarta Sans (Opsional, untuk tipografi premium modern) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Laravel Vite Asset Loading -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Tailwind CSS CDN Fallback/JIT -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    <style>
        /* Animasi Transisi Halus pada Input & Tombol */
        input, button, a {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Modifikasi Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="antialiased font-sans bg-slate-50 text-slate-900 min-h-screen selection:bg-blue-600 selection:text-white">

    <!-- Container Utama untuk Menyuntikkan View Login -->
    <main class="min-h-screen flex flex-col justify-center relative overflow-x-hidden">
        {{ $slot }}
    </main>

</body>
</html>