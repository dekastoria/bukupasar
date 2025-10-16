<?php

namespace Tests\Feature\EndToEnd;

use App\Models\Market;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TransactionFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2025-10-16 08:00:00');

        $this->seed();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_inputer_can_complete_daily_flow(): void
    {
        $market = Market::firstOrFail();

        $tenant = Tenant::create([
            'market_id' => $market->id,
            'nama' => 'Siti Bunga',
            'nomor_lapak' => 'A-01',
            'hp' => '081234567890',
            'outstanding' => 150_000,
        ]);

        $token = $this->loginAs('inputer', $market->id);

        $headers = ['Authorization' => 'Bearer '.$token];
        $today = now()->toDateString();

        $createIncome = $this->withHeaders($headers)->postJson('/api/transactions', [
            'tanggal' => $today,
            'jenis' => 'pemasukan',
            'subkategori' => 'Retribusi',
            'jumlah' => 100_000,
            'catatan' => 'Retribusi harian',
        ]);

        $createIncome->assertCreated();

        $createExpense = $this->withHeaders($headers)->postJson('/api/transactions', [
            'tanggal' => $today,
            'jenis' => 'pengeluaran',
            'subkategori' => 'Operasional',
            'jumlah' => 40_000,
            'catatan' => 'Biaya kebersihan',
        ]);

        $createExpense->assertCreated();

        $createPayment = $this->withHeaders($headers)->postJson('/api/payments', [
            'tenant_id' => $tenant->id,
            'tanggal' => $today,
            'jumlah' => 50_000,
            'catatan' => 'Bayar sewa kios',
        ]);

        $createPayment->assertCreated();

        $this->assertSame(100_000, $tenant->fresh()->outstanding);

        $dailyReport = $this->withHeaders($headers)->getJson('/api/reports/daily?date='.$today);

        $dailyReport->assertOk()
            ->assertJsonPath('date', $today)
            ->assertJsonPath('totals.pemasukan', 100_000)
            ->assertJsonPath('totals.pengeluaran', 40_000)
            ->assertJsonPath('saldo', 60_000)
            ->assertJsonCount(2, 'transactions');

        $summary = $this->withHeaders($headers)->getJson('/api/reports/summary?from='.$today.'&to='.$today);

        $summary->assertOk()
            ->assertJsonPath('totals.pemasukan', 100_000)
            ->assertJsonPath('totals.pengeluaran', 40_000)
            ->assertJsonPath('totals.saldo', 60_000);
    }

    public function test_inputer_cannot_edit_transaction_belonging_to_other_user(): void
    {
        $market = Market::firstOrFail();
        $admin = User::where('username', 'adminpasar')->firstOrFail();

        $transaction = Transaction::create([
            'market_id' => $market->id,
            'tanggal' => now()->toDateString(),
            'jenis' => 'pemasukan',
            'subkategori' => 'Retribusi',
            'jumlah' => 75_000,
            'created_by' => $admin->id,
        ]);

        $token = $this->loginAs('inputer', $market->id);

        $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
            ->putJson('/api/transactions/'.$transaction->id, [
                'catatan' => 'Mencoba edit milik admin',
            ]);

        $response->assertForbidden();
    }

    public function test_inputer_cannot_edit_transaction_after_edit_window(): void
    {
        $market = Market::firstOrFail();
        $inputer = User::where('username', 'inputer')->firstOrFail();

        $transaction = Transaction::create([
            'market_id' => $market->id,
            'tanggal' => now()->subDays(2)->toDateString(),
            'jenis' => 'pemasukan',
            'subkategori' => 'Retribusi',
            'jumlah' => 60_000,
            'created_by' => $inputer->id,
        ]);

        $transaction->forceFill([
            'created_at' => now()->subHours(30),
            'updated_at' => now()->subHours(30),
        ])->saveQuietly();

        $token = $this->loginAs('inputer', $market->id);

        $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
            ->putJson('/api/transactions/'.$transaction->id, [
                'catatan' => 'Mencoba edit setelah 24 jam',
            ]);

        $response->assertForbidden();
    }

    public function test_admin_pasar_can_manage_tenants_and_view_categories_through_api(): void
    {
        $market = Market::firstOrFail();
        $token = $this->loginAs('adminpasar', $market->id);
        $headers = ['Authorization' => 'Bearer '.$token];

        $createTenant = $this->withHeaders($headers)->postJson('/api/tenants', [
            'nama' => 'Budi Santoso',
            'nomor_lapak' => 'B-02',
            'hp' => '08991234567',
            'alamat' => 'Blok B No 2',
            'outstanding' => 0,
        ]);

        $createTenant->assertCreated();

        $tenantId = $createTenant->json('data.id');

        $listTenants = $this->withHeaders($headers)->getJson('/api/tenants');

        $listTenants->assertOk()
            ->assertJsonFragment([
                'id' => $tenantId,
                'nama' => 'Budi Santoso',
            ]);

        $categories = $this->withHeaders($headers)->getJson('/api/categories?jenis=pemasukan');

        $categories->assertOk()
            ->assertJsonFragment([
                'nama' => 'Retribusi',
                'jenis' => 'pemasukan',
            ]);
    }

    public function test_viewer_cannot_create_transactions_or_payments(): void
    {
        $market = Market::firstOrFail();
        $token = $this->loginAs('viewer', $market->id);
        $headers = ['Authorization' => 'Bearer '.$token];

        $createTransaction = $this->withHeaders($headers)->postJson('/api/transactions', [
            'tanggal' => now()->toDateString(),
            'jenis' => 'pemasukan',
            'subkategori' => 'Retribusi',
            'jumlah' => 10_000,
        ]);

        $createTransaction->assertForbidden();

        $tenant = Tenant::create([
            'market_id' => $market->id,
            'nama' => 'Tenant Viewer',
            'nomor_lapak' => 'V-01',
            'outstanding' => 20_000,
        ]);

        $createPayment = $this->withHeaders($headers)->postJson('/api/payments', [
            'tenant_id' => $tenant->id,
            'tanggal' => now()->toDateString(),
            'jumlah' => 10_000,
        ]);

        $createPayment->assertForbidden();
    }

    public function test_viewer_cannot_manage_tenants(): void
    {
        $market = Market::firstOrFail();
        $token = $this->loginAs('viewer', $market->id);

        $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])->postJson('/api/tenants', [
            'nama' => 'Unauthorized Tenant',
            'nomor_lapak' => 'U-01',
        ]);

        $response->assertForbidden();
    }

    public function test_login_is_rate_limited_after_too_many_attempts(): void
    {
        $market = Market::firstOrFail();

        for ($i = 0; $i < 10; $i++) {
            $attempt = $this->postJson('/api/auth/login', [
                'identifier' => 'inputer',
                'password' => 'wrong-password',
                'market_id' => $market->id,
            ]);

            $attempt->assertStatus(401);
        }

        $blocked = $this->postJson('/api/auth/login', [
            'identifier' => 'inputer',
            'password' => 'wrong-password',
            'market_id' => $market->id,
        ]);

        $blocked->assertStatus(429);
    }

    private function loginAs(string $identifier, int $marketId): string
    {
        $response = $this->postJson('/api/auth/login', [
            'identifier' => $identifier,
            'password' => 'password',
            'market_id' => $marketId,
        ]);

        $response->assertOk();

        return $response->json('data.token');
    }
}
