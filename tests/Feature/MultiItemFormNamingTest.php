<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

/**
 * Form multi-item wajib memakai index numerik items[N][field].
 *
 * PHP memecah "items[][field]" menjadi row TERPISAH per field (diverifikasi via
 * parse_str dan $_POST sungguhan di server PHP), sehingga penamaan items[][...]
 * merusak data browser — test Laravel tidak menangkapnya karena post() tidak
 * melewati parser form PHP.
 */
class MultiItemFormNamingTest extends TestCase
{
    private ?User $admin = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create([
            'name' => 'Tester Form Naming',
            'email' => 'form-naming-tester@example.test',
            'password' => 'secret-password',
            'role' => 'admin',
        ]);
    }

    protected function tearDown(): void
    {
        User::where('email', 'form-naming-tester@example.test')->delete();
        parent::tearDown();
    }

    public function test_form_pembelian_memakai_index_numerik(): void
    {
        $res = $this->actingAs($this->admin)->get(route('admin.purchases.create'));
        $res->assertOk();
        $res->assertSee('name="items[0][item_id]"', false);
        $res->assertSee('name="items[0][qty]"', false);
        $res->assertSee('name="items[0][harga]"', false);
        $res->assertDontSee('name="items[][', false);
        $res->assertSee('Ketik nama barang untuk mencari', false);
    }

    public function test_form_pengeluaran_memakai_index_numerik(): void
    {
        $res = $this->actingAs($this->admin)->get(route('admin.expenses.create'));
        $res->assertOk();
        $res->assertSee('name="items[0][uraian]"', false);
        $res->assertSee('name="items[0][qty]"', false);
        $res->assertDontSee('name="items[][', false);
    }

    public function test_php_mengelompokkan_field_dengan_index_numerik(): void
    {
        parse_str('items[0][item_id]=1&items[0][qty]=2&items[0][harga]=3000&items[1][item_id]=5', $r);
        $this->assertCount(2, $r['items']);
        $this->assertSame('2', $r['items'][0]['qty']);
        $this->assertSame('5', $r['items'][1]['item_id']);
    }
}
