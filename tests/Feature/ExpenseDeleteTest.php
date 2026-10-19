<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseItem;
use App\Models\Purchase;
use App\Models\TopUp;
use App\Models\User;
use Tests\TestCase;

/**
 * Hapus pengeluaran lewat index/form edit — hanya untuk pengeluaran manual
 * (bukan otomatis dari PO/maintenance). Saldo kas dihitung agregat
 * (sum topup - sum expense), sehingga pengeluaran yang dihapus otomatis
 * menaikkan saldo. Tanpa RefreshDatabase (koneksi MySQL dev) — baris uji
 * dibersihkan di tearDown.
 */
class ExpenseDeleteTest extends TestCase
{
    private ?User $admin = null;
    private array $expenseIds = [];
    private array $categoryIds = [];
    private array $purchaseIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create([
            'name' => 'Tester Expense Delete',
            'email' => 'expense-delete-tester@example.test',
            'password' => 'secret-password',
            'role' => 'admin',
        ]);
    }

    protected function tearDown(): void
    {
        Purchase::whereIn('id', $this->purchaseIds)->delete();
        ExpenseItem::whereIn('expense_id', $this->expenseIds)->delete();
        Expense::whereIn('id', $this->expenseIds)->delete();
        ExpenseCategory::whereIn('id', $this->categoryIds)->delete();
        User::where('email', 'expense-delete-tester@example.test')->delete();
        parent::tearDown();
    }

    private function makeExpense(int $jumlah): Expense
    {
        $kat = ExpenseCategory::create(['nama' => 'Uji Hapus Pengeluaran ' . uniqid()]);
        $this->categoryIds[] = $kat->id;
        $e = Expense::create([
            'nomor' => 'EXP-TEST-DEL-' . uniqid(),
            'expense_category_id' => $kat->id,
            'tanggal' => now()->toDateString(),
            'jumlah' => $jumlah,
            'keterangan' => 'Uji hapus pengeluaran',
        ]);
        $this->expenseIds[] = $e->id;
        ExpenseItem::create([
            'expense_id' => $e->id,
            'uraian' => 'Uji hapus pengeluaran',
            'qty' => 1,
            'harga_satuan' => $jumlah,
            'subtotal' => $jumlah,
        ]);

        return $e;
    }

    private function kunciSebagaiPembelian(Expense $e): Purchase
    {
        $p = Purchase::create([
            'nomor' => 'PO-TEST-DEL-' . uniqid(),
            'tanggal' => now()->toDateString(),
            'total' => $e->jumlah,
            'status' => 'selesai',
            'sumber_dana' => 'kas',
            'expense_id' => $e->id,
        ]);
        $this->purchaseIds[] = $p->id;

        return $p;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $e = $this->makeExpense(10000);

        $this->delete(route('admin.expenses.destroy', $e))->assertRedirect('/login');
        $this->assertDatabaseHas('expenses', ['id' => $e->id]);
    }

    public function test_pengeluaran_manual_bisa_dihapus_dan_saldo_naik(): void
    {
        $e = $this->makeExpense(50000);
        $saldoSebelum = TopUp::saldo();

        $this->actingAs($this->admin)
            ->delete(route('admin.expenses.destroy', $e))
            ->assertRedirect(route('admin.expenses.index'))
            ->assertSessionHas('ok');

        $this->assertDatabaseMissing('expenses', ['id' => $e->id]);
        $this->assertDatabaseMissing('expense_items', ['expense_id' => $e->id]);
        $this->assertEqualsWithDelta($saldoSebelum + 50000, TopUp::saldo(), 0.01);
    }

    public function test_pengeluaran_otomatis_dari_pembelian_tidak_bisa_dihapus(): void
    {
        $e = $this->makeExpense(75000);
        $this->kunciSebagaiPembelian($e);

        $this->actingAs($this->admin)
            ->from(route('admin.expenses.index'))
            ->delete(route('admin.expenses.destroy', $e))
            ->assertSessionHasErrors('kunci');

        $this->assertDatabaseHas('expenses', ['id' => $e->id]);
    }

    public function test_index_tombol_hapus_hanya_untuk_pengeluaran_manual(): void
    {
        $manual = $this->makeExpense(20000);
        $terkunci = $this->makeExpense(30000);
        $this->kunciSebagaiPembelian($terkunci);

        $res = $this->actingAs($this->admin)->get(route('admin.expenses.index'));
        $res->assertOk();
        $res->assertSee('action="' . route('admin.expenses.destroy', $manual) . '"', false);
        $res->assertDontSee('action="' . route('admin.expenses.destroy', $terkunci) . '"', false);
    }
}
