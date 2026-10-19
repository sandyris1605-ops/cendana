<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseItem;
use App\Models\TopUp;
use App\Models\User;
use Tests\TestCase;

/**
 * Pengeluaran multi-item: 1 transaksi = N baris (uraian x qty x harga).
 * Tanpa RefreshDatabase (koneksi MySQL dev) — semua baris uji dibersihkan di tearDown.
 */
class ExpenseMultiItemTest extends TestCase
{
    private ?User $admin = null;
    private array $expenseIds = [];
    private array $topupIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create([
            'name' => 'Tester Expense Multi',
            'email' => 'expense-multi-tester@example.test',
            'password' => 'secret-password',
            'role' => 'admin',
        ]);
    }

    protected function tearDown(): void
    {
        foreach ($this->expenseIds as $id) {
            ExpenseItem::where('expense_id', $id)->delete();
            Expense::where('id', $id)->delete();
        }
        foreach ($this->topupIds as $id) {
            TopUp::where('id', $id)->delete();
        }
        User::where('email', 'expense-multi-tester@example.test')->delete();
        parent::tearDown();
    }

    private function pastikanSaldoCukup(int $butuh): void
    {
        if (TopUp::saldo() < $butuh) {
            $t = TopUp::create(['tanggal' => now()->toDateString(), 'jumlah' => $butuh * 2, 'keterangan' => 'Uji expense multi']);
            $this->topupIds[] = $t->id;
        }
    }

    public function test_store_multi_item_menyimpan_n_baris_dan_total(): void
    {
        $this->pastikanSaldoCukup(500000);

        $res = $this->actingAs($this->admin)->post(route('admin.expenses.store'), [
            'tanggal' => now()->toDateString(),
            'items' => [
                ['uraian' => 'Beras 5 kg', 'qty' => 2, 'harga' => 75000],
                ['uraian' => 'Minyak 2 L', 'qty' => 1, 'harga' => 45000],
                ['uraian' => 'Gula 1 kg', 'qty' => 3, 'harga' => 18000],
            ],
        ]);
        $res->assertRedirect(route('admin.expenses.index'));

        $e = Expense::orderByDesc('id')->first();
        $this->expenseIds[] = $e->id;

        $this->assertSame(2 * 75000 + 1 * 45000 + 3 * 18000, (int) $e->jumlah);
        $this->assertCount(3, $e->items);
        $this->assertSame(150000, (int) $e->items->firstWhere('uraian', 'Beras 5 kg')->subtotal);
        $this->assertStringContainsString('Beras 5 kg x2', $e->keterangan);
    }

    public function test_store_ditolak_saat_total_melebihi_saldo(): void
    {
        $saldo = (int) TopUp::saldo();

        $res = $this->actingAs($this->admin)->post(route('admin.expenses.store'), [
            'tanggal' => now()->toDateString(),
            'items' => [['uraian' => 'Belanja raksasa uji', 'qty' => 1, 'harga' => $saldo + 1000000]],
        ]);

        $res->assertSessionHasErrors('items');
        $this->assertDatabaseMissing('expenses', ['keterangan' => 'Belanja raksasa uji x1']);
    }

    public function test_update_mengganti_baris_dan_total(): void
    {
        $this->pastikanSaldoCukup(500000);

        $this->actingAs($this->admin)->post(route('admin.expenses.store'), [
            'tanggal' => now()->toDateString(),
            'items' => [['uraian' => 'Item lama uji', 'qty' => 1, 'harga' => 50000]],
        ]);
        $e = Expense::orderByDesc('id')->first();
        $this->expenseIds[] = $e->id;

        $this->actingAs($this->admin)->put(route('admin.expenses.update', $e), [
            'tanggal' => now()->toDateString(),
            'items' => [
                ['uraian' => 'Item baru A uji', 'qty' => 2, 'harga' => 30000],
                ['uraian' => 'Item baru B uji', 'qty' => 1, 'harga' => 20000],
            ],
        ])->assertRedirect(route('admin.expenses.index'));

        $e->refresh();
        $this->assertSame(80000, (int) $e->jumlah);
        $this->assertCount(2, $e->items);
        $this->assertNull($e->items->firstWhere('uraian', 'Item lama uji'));
    }

    public function test_form_tidak_lagi_memakai_dropdown_kategori(): void
    {
        $html = $this->actingAs($this->admin)->get(route('admin.expenses.create'))->assertOk()->getContent();

        $this->assertStringContainsString('Tambah Item', $html);
        $this->assertStringNotContainsString('expense_category_id', $html);
    }

    public function test_index_menampilkan_jumlah_item(): void
    {
        $html = $this->actingAs($this->admin)->get(route('admin.expenses.index'))->assertOk()->getContent();

        $this->assertStringContainsString('item', $html);
    }
}
