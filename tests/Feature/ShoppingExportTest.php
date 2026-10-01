<?php

namespace Tests\Feature;

use App\Models\Shopping;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShoppingExportTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Admin Export Test',
            'username' => 'adminexport',
            'code' => 'ADMEXP',
            'email' => 'adminexport@test.com',
            'password' => bcrypt('password'),
            'role' => 'Admin',
        ]);

        Shopping::create([
            'invoice' => 'INV-TEST-001',
            'sales_type' => 'offline',
            'user_id' => $this->user->id,
            'total_price' => 50000,
            'pay' => 60000,
            'change' => 10000,
        ]);
    }

    public function test_can_access_shopping_export_preview_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('kasir.shopping.export'));

        $response->assertStatus(200);
        $response->assertSee('Export Data Penjualan');
        $response->assertSee('INV-TEST-001');
    }

    public function test_can_access_shopping_print_view(): void
    {
        $response = $this->get(route('data.shopping.print'));

        $response->assertStatus(200);
        $response->assertSee('LAPORAN DATA PENJUALAN');
        $response->assertSee('INV-TEST-001');
        $response->assertSee('window.print()', false);
    }

    public function test_can_stream_shopping_pdf(): void
    {
        $response = $this->get(route('data.shopping.pdf'));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
    }

    public function test_can_download_shopping_excel(): void
    {
        $response = $this->get(route('data.shopping.excel'));

        $response->assertStatus(200);
        $this->assertTrue(
            str_contains($response->headers->get('content-disposition') ?? '', 'Data-Penjualan.xlsx')
        );
    }
}
