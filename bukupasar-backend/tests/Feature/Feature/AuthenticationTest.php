<?php

namespace Tests\Feature\Feature;

use App\Models\Market;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected Market $market;
    protected User $adminPusat;
    protected User $adminPasar;
    protected User $inputer;
    protected User $viewer;

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

        // Create users for each role
        $this->adminPusat = User::create([
            'market_id' => $this->market->id,
            'username' => 'adminpusat',
            'name' => 'Admin Pusat',
            'email' => 'adminpusat@test.com',
            'password' => Hash::make('password'),
        ]);
        $this->adminPusat->assignRole('admin_pusat');

        $this->adminPasar = User::create([
            'market_id' => $this->market->id,
            'username' => 'adminpasar',
            'name' => 'Admin Pasar',
            'email' => 'adminpasar@test.com',
            'password' => Hash::make('password'),
        ]);
        $this->adminPasar->assignRole('admin_pasar');

        $this->inputer = User::create([
            'market_id' => $this->market->id,
            'username' => 'inputer',
            'name' => 'Inputer',
            'email' => 'inputer@test.com',
            'password' => Hash::make('password'),
        ]);
        $this->inputer->assignRole('inputer');

        $this->viewer = User::create([
            'market_id' => $this->market->id,
            'username' => 'viewer',
            'name' => 'Viewer',
            'email' => 'viewer@test.com',
            'password' => Hash::make('password'),
        ]);
        $this->viewer->assignRole('viewer');
    }

    /** @test */
    public function admin_pusat_can_login_successfully()
    {
        $response = $this->postJson('/api/auth/login', [
            'identifier' => 'adminpusat',
            'password' => 'password',
            'market_id' => $this->market->id,
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'data' => [
                         'token',
                         'user' => ['id', 'name', 'username', 'market_id', 'role'],
                     ],
                 ])
                 ->assertJson([
                     'data' => [
                         'user' => [
                             'username' => 'adminpusat',
                             'role' => 'admin_pusat',
                         ],
                     ],
                 ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    /** @test */
    public function admin_pasar_can_login_successfully()
    {
        $response = $this->postJson('/api/auth/login', [
            'identifier' => 'adminpasar',
            'password' => 'password',
            'market_id' => $this->market->id,
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'data' => [
                         'user' => [
                             'username' => 'adminpasar',
                             'role' => 'admin_pasar',
                         ],
                     ],
                 ]);
    }

    /** @test */
    public function inputer_can_login_successfully()
    {
        $response = $this->postJson('/api/auth/login', [
            'identifier' => 'inputer',
            'password' => 'password',
            'market_id' => $this->market->id,
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'data' => [
                         'user' => [
                             'username' => 'inputer',
                             'role' => 'inputer',
                         ],
                     ],
                 ]);
    }

    /** @test */
    public function viewer_can_login_successfully()
    {
        $response = $this->postJson('/api/auth/login', [
            'identifier' => 'viewer',
            'password' => 'password',
            'market_id' => $this->market->id,
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'data' => [
                         'user' => [
                             'username' => 'viewer',
                             'role' => 'viewer',
                         ],
                     ],
                 ]);
    }

    /** @test */
    public function login_fails_with_invalid_credentials()
    {
        $response = $this->postJson('/api/auth/login', [
            'identifier' => 'invalid',
            'password' => 'wrongpass',
            'market_id' => $this->market->id,
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'message' => 'Email/username atau password salah.',
                 ]);
    }

    /** @test */
    public function login_fails_with_wrong_password()
    {
        $response = $this->postJson('/api/auth/login', [
            'identifier' => 'inputer',
            'password' => 'wrongpassword',
            'market_id' => $this->market->id,
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function login_fails_with_wrong_market_id()
    {
        $response = $this->postJson('/api/auth/login', [
            'identifier' => 'inputer',
            'password' => 'password',
            'market_id' => 999, // Non-existent market
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['market_id']);
    }

    /** @test */
    public function login_requires_username_password_and_market_id()
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['identifier', 'password', 'market_id']);
    }

    /** @test */
    public function authenticated_user_can_logout()
    {
        $token = $this->adminPasar->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->postJson('/api/auth/logout');

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Logout berhasil.',
                 ]);

        // Token should be revoked
        $this->adminPasar->refresh();
        $this->assertCount(0, $this->adminPasar->tokens);
    }

    /** @test */
    public function authenticated_user_can_get_user_info()
    {
        $token = $this->inputer->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->getJson('/api/auth/user');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => ['id', 'name', 'username', 'market_id', 'role'],
                 ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_protected_routes()
    {
        $response = $this->getJson('/api/auth/user');

        $response->assertStatus(401);
    }
}
