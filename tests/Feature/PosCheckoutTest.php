<?php

namespace Tests\Feature;

use App\Models\CashShift;
use App\Models\Inventory;
use App\Models\Package;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Standar #8 (Maintainability): test ini adalah jaring pengaman —
 * fitur baru (mis. "cicilan") tidak boleh merusak alur kasir & shift.
 */
class PosCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;
    private CashShift $shift;

    protected function setUp(): void
    {
        parent::setUp();

        // CSRF hanya relevan untuk browser; test mengirim JSON langsung.
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $this->withSession(['_token' => 'test-token']);
        $this->withHeaders(['X-CSRF-TOKEN' => 'test-token']);

        $this->tenant = Tenant::create([
            'nama_toko' => 'Toko Test',
            'expired_date' => now()->addYear(),
        ]);

        $this->user = User::create([
            'name' => 'Kasir Test',
            'email' => 'kasir@test.local',
            'password' => 'secret123',
            'role' => User::ROLE_TENANT,
            'tenant_id' => $this->tenant->id,
        ]);

        $this->shift = CashShift::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'opening_amount' => 100_000,
            'expected_amount' => 100_000,
            'opened_at' => now(),
        ]);
    }

    private function unit(int $price): Inventory
    {
        $product = Product::create([
            'tenant_id' => $this->tenant->id,
            'tipe_hp' => 'Model T',
            'price_sell' => $price,
        ]);

        return Inventory::create([
            'tenant_id' => $this->tenant->id,
            'product_id' => $product->id,
            'imei' => (string) random_int(100000000000000, 999999999999999),
            'status' => Inventory::STATUS_AVAILABLE,
            'entry_date' => now()->toDateString(),
        ]);
    }

    public function test_checkout_satu_item_tanpa_diskon(): void
    {
        $item = $this->unit(2_500_000);

        $res = $this->actingAs($this->user)->postJson(route('tenant.pos.checkout'), [
            'items' => [$item->id],
            'payment_method' => 'cash',
        ]);

        $res->assertOk()->assertJsonPath('total_price', 2_500_000);
        $this->assertSame(Inventory::STATUS_SOLD, $item->fresh()->status);
        $this->assertSame(2_600_000, (int) $this->shift->fresh()->expected_amount);
    }

    public function test_checkout_diskon_persen_proporsional_per_item(): void
    {
        $a = $this->unit(10_000_000);
        $b = $this->unit(10_000_000);

        $res = $this->actingAs($this->user)->postJson(route('tenant.pos.checkout'), [
            'items' => [$a->id, $b->id],
            'discount_type' => 'percent',
            'discount_value' => 10,
            'payment_method' => 'qris',
        ]);

        $res->assertOk()
            ->assertJsonPath('gross', 20_000_000)
            ->assertJsonPath('discount', 2_000_000)
            ->assertJsonPath('total_price', 18_000_000);

        // total final_price per item harus jumlah ke total nota
        $this->assertSame(
            18_000_000,
            (int) \App\Models\Transaction::where('nota_id', $res->json('nota_id'))->sum('final_price')
        );
    }

    public function test_checkout_mixed_harus_pas(): void
    {
        $item = $this->unit(1_000_000);

        $this->actingAs($this->user)->postJson(route('tenant.pos.checkout'), [
            'items' => [$item->id],
            'payment_method' => 'mixed',
            'payment' => ['cash' => 400_000, 'qris' => 0, 'transfer' => 0],
        ])->assertStatus(422);

        $this->assertSame(Inventory::STATUS_AVAILABLE, $item->fresh()->status);
    }

    public function test_unit_terjual_tidak_bisa_di_checkout_lagi(): void
    {
        $item = $this->unit(500_000);

        $this->actingAs($this->user)->postJson(route('tenant.pos.checkout'), [
            'items' => [$item->id], 'payment_method' => 'cash',
        ])->assertOk();

        $this->actingAs($this->user)->postJson(route('tenant.pos.checkout'), [
            'items' => [$item->id], 'payment_method' => 'cash',
        ])->assertStatus(422);
    }
}
