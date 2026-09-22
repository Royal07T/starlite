<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Models\UserWallet;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletServiceTest extends TestCase
{
    use RefreshDatabase;

    protected WalletService $walletService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->walletService = new WalletService();
    }

    public function test_deduct_funds_success(): void
    {
        $user = User::factory()->create();
        UserWallet::create([
            'user_id' => $user->id,
            'amount' => 100.00,
        ]);

        $result = $this->walletService->deductFunds($user->id, 50.00);

        $this->assertNotNull($result);
        $this->assertDatabaseHas('user_wallets', [
            'user_id' => $user->id,
            'amount' => 50.00,
        ]);
    }

    public function test_deduct_funds_insufficient_balance_throws_exception(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient wallet balance');

        $user = User::factory()->create();
        UserWallet::create([
            'user_id' => $user->id,
            'amount' => 30.00,
        ]);

        $this->walletService->deductFunds($user->id, 50.00);
    }

    public function test_deduct_funds_exact_balance(): void
    {
        $user = User::factory()->create();
        UserWallet::create([
            'user_id' => $user->id,
            'amount' => 50.00,
        ]);

        $result = $this->walletService->deductFunds($user->id, 50.00);

        $this->assertNotNull($result);
        $this->assertDatabaseHas('user_wallets', [
            'user_id' => $user->id,
            'amount' => 0.00,
        ]);
    }

    public function test_add_funds(): void
    {
        $user = User::factory()->create();
        UserWallet::create([
            'user_id' => $user->id,
            'amount' => 50.00,
        ]);

        $result = $this->walletService->addFunds($user->id, 75.00);

        $this->assertNotNull($result);
        $this->assertDatabaseHas('user_wallets', [
            'user_id' => $user->id,
            'amount' => 125.00,
        ]);
    }

    public function test_get_wallet_amount(): void
    {
        $user = User::factory()->create();
        UserWallet::create([
            'user_id' => $user->id,
            'amount' => 250.00,
        ]);

        $amount = $this->walletService->getWalletAmount($user->id);

        $this->assertEquals(250.00, $amount);
    }

    public function test_get_wallet_amount_no_wallet_returns_zero(): void
    {
        $user = User::factory()->create();

        $amount = $this->walletService->getWalletAmount($user->id);

        $this->assertEquals(0, $amount);
    }

    public function test_get_user_wallet_creates_wallet_if_not_exists(): void
    {
        $user = User::factory()->create();

        $wallet = $this->walletService->getUserWallet($user->id);

        $this->assertNotNull($wallet);
        $this->assertEquals($user->id, $wallet->user_id);
        $this->assertDatabaseHas('user_wallets', ['user_id' => $user->id]);
    }
}
