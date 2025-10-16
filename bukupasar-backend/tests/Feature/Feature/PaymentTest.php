<?php

namespace Tests\Feature\Feature;

use App\Models\Market;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected Market $market;
    protected User $inputer;
    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test market
        $this->market = Market::create([
            'name' => 'Test Market',
            'code' => 'TEST01',
            'address' => 'Test Address',
        ]);

        // Create roles
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);

        // Create inputer user
        $this->inputer = User::create([
            'market_id' => $this->market->id,
            'username' => 'inputer',
            'name' => 'Inputer',
            'email' => 'inputer@test.com',
            'password' => Hash::make('password'),
        ]);
        $this->inputer->assignRole('inputer');

        // Create tenant with outstanding
        $this->tenant = Tenant::create([
            'market_id' => $this->market->id,
            'nama' => 'Test Tenant',
            'nomor_lapak' => 'A01',
            'outstanding' => 500000, // Rp 500,000
        ]);
    }

    /** @test */
    public function can_create_payment_within_outstanding()
    {
        $token = $this->inputer->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->postJson('/api/payments', [
                             'tenant_id' => $this->tenant->id,
                             'tanggal' => today()->format('Y-m-d'),
                             'jumlah' => 200000, // Less than outstanding
                         ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'data' => ['id', 'tenant_id', 'tanggal', 'jumlah'],
                 ]);

        // Check outstanding updated
        $this->tenant->refresh();
        $this->assertEquals(300000, $this->tenant->outstanding);
    }

    /** @test */
    public function can_make_full_payment()
    {
        $token = $this->inputer->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->postJson('/api/payments', [
                             'tenant_id' => $this->tenant->id,
                             'tanggal' => today()->format('Y-m-d'),
                             'jumlah' => 500000, // Exact outstanding amount
                         ]);

        $response->assertStatus(201);

        // Outstanding should be zero
        $this->tenant->refresh();
        $this->assertEquals(0, $this->tenant->outstanding);
    }

    /** @test */
    public function cannot_pay_more_than_outstanding()
    {
        $token = $this->inputer->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->postJson('/api/payments', [
                             'tenant_id' => $this->tenant->id,
                             'tanggal' => today()->format('Y-m-d'),
                             'jumlah' => 600000, // More than outstanding
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['jumlah']);

        // Outstanding should remain unchanged
        $this->tenant->refresh();
        $this->assertEquals(500000, $this->tenant->outstanding);
    }

    /** @test */
    public function payment_requires_valid_tenant()
    {
        $token = $this->inputer->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->postJson('/api/payments', [
                             'tenant_id' => 999, // Non-existent tenant
                             'tanggal' => today()->format('Y-m-d'),
                             'jumlah' => 100000,
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['tenant_id']);
    }

    /** @test */
    public function payment_requires_positive_amount()
    {
        $token = $this->inputer->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->postJson('/api/payments', [
                             'tenant_id' => $this->tenant->id,
                             'tanggal' => today()->format('Y-m-d'),
                             'jumlah' => 0,
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['jumlah']);
    }

    /** @test */
    public function payment_requires_tanggal()
    {
        $token = $this->inputer->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->postJson('/api/payments', [
                             'tenant_id' => $this->tenant->id,
                             'jumlah' => 100000,
                             // Missing tanggal
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['tanggal']);
    }

    /** @test */
    public function multiple_payments_update_outstanding_correctly()
    {
        $token = $this->inputer->createToken('test')->plainTextToken;

        // First payment: Rp 200,000
        $this->withHeader('Authorization', "Bearer {$token}")
             ->postJson('/api/payments', [
                 'tenant_id' => $this->tenant->id,
                 'tanggal' => today()->format('Y-m-d'),
                 'jumlah' => 200000,
             ]);

        $this->tenant->refresh();
        $this->assertEquals(300000, $this->tenant->outstanding);

        // Second payment: Rp 100,000
        $this->withHeader('Authorization', "Bearer {$token}")
             ->postJson('/api/payments', [
                 'tenant_id' => $this->tenant->id,
                 'tanggal' => today()->format('Y-m-d'),
                 'jumlah' => 100000,
             ]);

        $this->tenant->refresh();
        $this->assertEquals(200000, $this->tenant->outstanding);

        // Third payment: Rp 200,000 (full remaining)
        $this->withHeader('Authorization', "Bearer {$token}")
             ->postJson('/api/payments', [
                 'tenant_id' => $this->tenant->id,
                 'tanggal' => today()->format('Y-m-d'),
                 'jumlah' => 200000,
             ]);

        $this->tenant->refresh();
        $this->assertEquals(0, $this->tenant->outstanding);
    }

    /** @test */
    public function payment_records_creator()
    {
        $token = $this->inputer->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->postJson('/api/payments', [
                             'tenant_id' => $this->tenant->id,
                             'tanggal' => today()->format('Y-m-d'),
                             'jumlah' => 100000,
                         ]);

        $response->assertStatus(201);

        $payment = Payment::latest()->first();
        $this->assertEquals($this->inputer->id, $payment->created_by);
        $this->assertEquals($this->market->id, $payment->market_id);
    }

    /** @test */
    public function cannot_pay_for_tenant_in_different_market()
    {
        // Create another market with tenant
        $market2 = Market::create([
            'name' => 'Market 2',
            'code' => 'TEST02',
            'address' => 'Address 2',
        ]);

        $tenant2 = Tenant::create([
            'market_id' => $market2->id,
            'nama' => 'Tenant Market 2',
            'nomor_lapak' => 'B01',
            'outstanding' => 100000,
        ]);

        $token = $this->inputer->createToken('test')->plainTextToken;

        // Try to pay for tenant in different market
        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->postJson('/api/payments', [
                             'tenant_id' => $tenant2->id,
                             'tanggal' => today()->format('Y-m-d'),
                             'jumlah' => 50000,
                         ]);

        // Should get validation error because tenant doesn't exist in user's market
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['tenant_id']);
    }

    /** @test */
    public function payment_history_is_recorded()
    {
        $token = $this->inputer->createToken('test')->plainTextToken;

        // Make 3 payments
        for ($i = 1; $i <= 3; $i++) {
            $this->withHeader('Authorization', "Bearer {$token}")
                 ->postJson('/api/payments', [
                     'tenant_id' => $this->tenant->id,
                     'tanggal' => today()->format('Y-m-d'),
                     'jumlah' => 100000,
                     'catatan' => "Payment #{$i}",
                 ]);
        }

        // Check payment history
        $payments = Payment::where('tenant_id', $this->tenant->id)->get();
        $this->assertCount(3, $payments);
        
        // Check total paid
        $totalPaid = $payments->sum('jumlah');
        $this->assertEquals(300000, $totalPaid);

        // Check remaining outstanding
        $this->tenant->refresh();
        $this->assertEquals(200000, $this->tenant->outstanding);
    }
}
