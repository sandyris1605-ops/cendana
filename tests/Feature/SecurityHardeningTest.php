<?php

namespace Tests\Feature;

use App\Models\Booking;
use Tests\TestCase;

/**
 * Pengaman deploy: anti brute-force login, anti-spam booking, header keamanan.
 * Cache driver tes = array (terisolasi per test) sehingga rate-limit aman diuji.
 */
class SecurityHardeningTest extends TestCase
{
    protected function tearDown(): void
    {
        Booking::where('telepon', 'like', '08111999%')->delete();
        parent::tearDown();
    }

    // ---------- Anti brute-force login ----------

    public function test_login_dibatasi_5_percobaan_per_menit(): void
    {
        // 5 percobaan pertama boleh (302 back + error), percobaan ke-6 → 429.
        for ($i = 1; $i <= 5; $i++) {
            $this->post('/login', ['email' => 'pencoba' . $i . '@example.test', 'password' => 'salah'])
                ->assertStatus(302);
        }
        // Percobaan ke-6 dalam rentang 1 menit → diblokir.
        $this->post('/login', ['email' => 'pencoba6@example.test', 'password' => 'salah'])
            ->assertStatus(429);
    }

    public function test_login_yang_sudah_salah_5_tetap_302_bukan_500(): void
    {
        $this->post('/login', ['email' => 'bukan-email-ada', 'password' => 'x'])
            ->assertStatus(302);
    }

    // ---------- Anti-spam booking (honeypot) ----------

    public function test_booking_honeypot_terisi_dibuang_tanpa_disimpan(): void
    {
        $this->from('/')->post('/booking', [
            'nama' => 'Bot Iklan', 'telepon' => '081119990001',
            'website' => 'http://spam-iklan.example', // bot mengisi honeypot
        ])->assertRedirect('/')->assertSessionHas('ok');

        $this->assertDatabaseMissing('bookings', ['telepon' => '081119990001']);
    }

    public function test_booking_tanpa_field_honeypot_dibuang(): void
    {
        // Bot yang tidak mengirimkan field tersembunyi sama sekali → tetap dibuang.
        $this->from('/')->post('/booking', [
            'nama' => 'Bot Telanjang', 'telepon' => '081119990002',
        ])->assertRedirect('/')->assertSessionHas('ok');

        $this->assertDatabaseMissing('bookings', ['telepon' => '081119990002']);
    }

    public function test_booking_normal_dengan_honeypot_kosong_disimpan(): void
    {
        // Manusia: field honeypot ada tapi KOSONG → lolos & tersimpan.
        $this->from('/')->post('/booking', [
            'nama' => 'Calon Penghuni', 'telepon' => '081119990003',
            'website' => '',
        ])->assertRedirect('/')->assertSessionHas('ok');

        $this->assertDatabaseHas('bookings', ['telepon' => '081119990003', 'nama' => 'Calon Penghuni']);
    }

    public function test_booking_dibatasi_rate_limit_percobaan_ke7_429(): void
    {
        $payload = ['nama' => 'Rate Bot', 'telepon' => '081119990004', 'website' => ''];
        for ($i = 1; $i <= 6; $i++) {
            $this->post('/booking', $payload);
        }
        // throttle:6,1 → percobaan ke-7 dalam 1 menit diblokir.
        $this->post('/booking', $payload)->assertStatus(429);
    }

    // ---------- Header keamanan ----------

    public function test_header_keamanan_ada_di_respons_publik(): void
    {
        $res = $this->get('/');
        $res->assertOk();
        $res->assertHeader('X-Content-Type-Options', 'nosniff');
        $res->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $res->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }
}