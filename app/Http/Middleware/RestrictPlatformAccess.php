<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RestrictPlatformAccess
{
    public function handle(Request $request, Closure $next, string $platform): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // 🌟 JALUR KHUSUS ANTI-RIBET: Jika dia Super Admin, loloskan langsung!
        // Ini mencegah Anda ter-logout paksa saat menguji aplikasi.
        if ($user->hasRole('super-admin')) {
            return $next($request);
        }
        
        // 📱 Deteksi gawai (Mobile vs Desktop) secara real-time
        $userAgent = $request->header('User-Agent');
        $isMobileDevice = (bool) preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $userAgent);

        // 🖥️ 1. JALUR DESKTOP ADMIN (Dilewati dari route /admin/*)
        if ($platform === 'desktop') {
            // Cek apakah akun ini punya hak membuka desktop sama sekali
            if (!$user->hasPermissionTo('access-desktop')) {
                Auth::logout();
                return redirect()->route('login')->withErrors([
                    'email' => 'Akun Anda tidak memiliki otorisasi untuk mengakses platform Desktop.'
                ]);
            }

            // Jika punya hak desktop, TAPI dia iseng buka lewat HP/Mobile Device:
            if ($isMobileDevice) {
                if ($user->hasPermissionTo('access-mobile')) {
                    return redirect()->route('mobile.dashboard'); 
                }
                
                Auth::logout();
                return redirect()->route('login')->withErrors([
                    'email' => 'Platform ini harus diakses menggunakan perangkat Komputer/PC Desktop.'
                ]);
            }
        }

        // 📱 2. JALUR MOBILE APPLICATION (Dilewati dari route /mobile/*)
        if ($platform === 'mobile') {
            // Cek apakah akun ini punya hak membuka mobile aplikasi
            if (!$user->hasPermissionTo('access-mobile')) {
                Auth::logout();
                return redirect()->route('login')->withErrors([
                    'email' => 'Akun Anda tidak memiliki otorisasi untuk menggunakan aplikasi Mobile.'
                ]);
            }

            // Jika dia buka jalur mobile menggunakan browser PC/Laptop (Bukan Mobile Device):
            if (!$isMobileDevice && $user->hasPermissionTo('access-desktop')) {
                return redirect()->route('admin.dashboard');
            }
        }

        return $next($request);
    }
}