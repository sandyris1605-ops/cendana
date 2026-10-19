<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Tests\TestCase;

/**
 * Tests for the dashboard "Saldo Kas 6 Bulan" running-balance chart.
 *
 * Deliberately does NOT use RefreshDatabase: phpunit.xml keeps the dev MySQL
 * connection, so every row created here is removed in tearDown() instead.
 */
class DashboardSaldoKasTest extends TestCase
{
    private ?User $admin = null;
    private ?Expense $expense = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create([
            'name' => 'Tester Dashboard Saldo',
            'email' => 'dashboard-saldo-tester@example.test',
            'password' => 'secret-password',
            'role' => 'admin',
        ]);
    }

    protected function tearDown(): void
    {
        if ($this->expense) {
            Expense::where('id', $this->expense->id)->delete();
        }
        ExpenseCategory::where('nama', 'Uji Dashboard Saldo')->delete();
        User::where('email', 'dashboard-saldo-tester@example.test')->delete();
        parent::tearDown();
    }

    /** Ambil dataset `saldo6` dari Chart.js di halaman dashboard. */
    private function saldo6FromDashboard(): array
    {
        $html = $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk()->getContent();

        preg_match('/data:\s*(\[[^\]]*\])\s*,\s*borderColor/s', $html, $m);
        $this->assertNotEmpty($m, 'Dataset chart saldo tidak ditemukan di dashboard.');

        return json_decode($m[1], true);
    }

    public function test_dashboard_renders_running_balance_chart(): void
    {
        $saldo6 = $this->saldo6FromDashboard();

        $this->assertCount(6, $saldo6, 'Grafik harus menampilkan 6 bulan.');
        $this->assertStringContainsString('Saldo Kas 6 Bulan', $this->actingAs($this->admin)->get(route('admin.dashboard'))->getContent());
    }

    public function test_last_point_equals_saldo_kas_page_balance(): void
    {
        $saldo6 = $this->saldo6FromDashboard();
        $terakhir = end($saldo6);

        $this->assertSame(
            (int) round(\App\Models\TopUp::saldo()),
            $terakhir,
            'Titik terakhir grafik harus sama dengan saldo halaman Saldo Kas.'
        );
    }

    public function test_new_expense_lowers_last_point_by_its_amount(): void
    {
        $awal = $this->saldo6FromDashboard();
        $sebelum = end($awal);

        $kat = ExpenseCategory::firstOrCreate(['nama' => 'Uji Dashboard Saldo']);
        $this->expense = Expense::create([
            'nomor' => 'EXP-TEST-DASH-SALDO',
            'tanggal' => now()->toDateString(),
            'expense_category_id' => $kat->id,
            'jumlah' => 123456,
            'keterangan' => 'Uji grafik saldo kas',
        ]);

        $akhir = $this->saldo6FromDashboard();
        $sesudah = end($akhir);

        $this->assertSame($sebelum - 123456, $sesudah, 'Expense bulan ini harus menurunkan saldo berjalan sebesar jumlahnya.');
        $this->assertSame((int) round(\App\Models\TopUp::saldo()), $sesudah);
    }

    public function test_chart_no_longer_uses_rent_payment_series(): void
    {
        $html = $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk()->getContent();

        $this->assertStringNotContainsString('masuk6', $html, 'Series lama (masuk6) tidak boleh tersisa.');
        $this->assertStringNotContainsString('keluar6', $html, 'Series lama (keluar6) tidak boleh tersisa.');
    }
}
