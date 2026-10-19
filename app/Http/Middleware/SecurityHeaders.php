<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Header keamanan dasar untuk semua respons (anti penyusup / klikjacking / sniffing).
 * Sengaja TIDAK memasang CSP ketat agar CDN eksternal (Tailwind, Font-Awesome)
 * tetap berfungsi.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Cegah MIME-sniffing (browser menebak tipe konten → risiko eksekusi).
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        // Cegah clickjacking — situs lain tak boleh menempel halaman ini di iframe asing.
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        // Batasi info referrer yang bocor ke situs lain.
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        // Matikan fitur browser berisiko (kamera/mikrofon otomatis dkk di luar konteks).
        $response->headers->set('Permissions-Policy', 'geolocation=(self), camera=(self), microphone=()');
        // Kurangi jejak versi framework/penerbit.
        $response->headers->remove('X-Powered-By');

        return $response;
    }
}