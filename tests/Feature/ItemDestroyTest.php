<?php

namespace Tests\Feature;

use App\Models\AssetUnit;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\User;
use Tests\TestCase;

/**
 * Tests for DELETE admin/items/{item} (Katalog Barang - hapus barang).
 *
 * Deliberately does NOT use RefreshDatabase: phpunit.xml keeps the dev MySQL
 * connection, so every row created here is removed in tearDown() instead.
 */
class ItemDestroyTest extends TestCase
{
    private ?User $admin = null;
    private ?Item $item = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create([
            'name' => 'Tester Item Destroy',
            'email' => 'item-destroy-tester@example.test',
            'password' => 'secret-password',
            'role' => 'admin',
        ]);
    }

    protected function tearDown(): void
    {
        if ($this->item) {
            AssetUnit::where('item_id', $this->item->id)->delete();
            PurchaseItem::where('item_id', $this->item->id)->delete();
            Item::where('id', $this->item->id)->delete();
        }
        Purchase::where('nomor', 'PO-TEST-ITEM-DESTROY')->delete();
        User::where('email', 'item-destroy-tester@example.test')->delete();
        parent::tearDown();
    }

    private function makeItem(): Item
    {
        $this->item = Item::create([
            'nama' => 'Barang Uji Hapus',
            'umur_pakai_bulan' => 12,
            'harga_estimasi' => 1000,
        ]);

        return $this->item;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $item = $this->makeItem();

        $this->delete(route('admin.items.destroy', $item))->assertRedirect('/login');
        $this->assertDatabaseHas('items', ['id' => $item->id]);
    }

    public function test_index_page_shows_delete_button(): void
    {
        $item = $this->makeItem();

        $this->actingAs($this->admin)
            ->get(route('admin.items.index'))
            ->assertOk()
            ->assertSee(route('admin.items.destroy', $item), false)
            ->assertSee('href="' . route('admin.items.show', $item) . '"', false)
            ->assertSee('Hapus');
    }

    public function test_detail_page_shows_delete_button(): void
    {
        $item = $this->makeItem();

        $this->actingAs($this->admin)
            ->get(route('admin.items.show', $item))
            ->assertOk()
            ->assertSee(route('admin.items.destroy', $item), false)
            ->assertSee('Hapus');
    }

    public function test_admin_can_delete_item_without_units(): void
    {
        $item = $this->makeItem();

        $this->actingAs($this->admin)
            ->delete(route('admin.items.destroy', $item))
            ->assertSessionHas('ok', 'Barang dihapus.');

        $this->assertDatabaseMissing('items', ['id' => $item->id]);
        $this->item = null;
    }

    public function test_item_with_units_cannot_be_deleted(): void
    {
        $item = $this->makeItem();
        AssetUnit::create([
            'kode_barcode' => 'CND-TEST-DESTROY-0001',
            'item_id' => $item->id,
            'tanggal_beli' => now()->toDateString(),
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.items.destroy', $item))
            ->assertSessionHas('ok', 'Tidak dapat dihapus karena masih memiliki 1 unit (barcode aktif). Hapus unitnya dulu di menu Aset/Pembelian.');

        $this->assertDatabaseHas('items', ['id' => $item->id]);
        $this->assertDatabaseHas('asset_units', ['kode_barcode' => 'CND-TEST-DESTROY-0001']);
        AssetUnit::where('item_id', $item->id)->delete();
    }

    public function test_item_with_purchase_history_cannot_be_deleted(): void
    {
        $item = $this->makeItem();
        $purchase = Purchase::create([
            'nomor' => 'PO-TEST-ITEM-DESTROY',
            'tanggal' => now()->toDateString(),
            'total' => 1000,
            'status' => 'selesai',
        ]);
        PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'item_id' => $item->id,
            'qty' => 1,
            'harga_satuan' => 1000,
            'subtotal' => 1000,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.items.destroy', $item))
            ->assertSessionHas('ok', 'Tidak dapat dihapus karena tercatat di riwayat pembelian.');

        $this->assertDatabaseHas('items', ['id' => $item->id]);
        $this->assertDatabaseHas('purchase_items', ['item_id' => $item->id]);
    }
}
