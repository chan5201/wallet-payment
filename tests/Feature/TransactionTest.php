<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

use Tests\TestCase;
use App\Models\User;
use App\Models\Transfer;

class TransactionTest extends TestCase
{
//    use RefreshDatabase;

    public function test_authenticated_user_can_view_own_transaction()
    {
        $user = User::factory()->create();

        $transaction = Transfer::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson("/api/transactions/{$transaction->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'success',
                'data' => [
                    'transaction_amount' => "{$transaction->amount}",
                    'status' => $transaction->status,
                    'trans_date' => $transaction->created_at->format('c'),
                ]
            ]);
    }

    public function test_user_cannot_view_other_users_transaction()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $transaction = Transfer::factory()->create(['user_id' => $user1->id]);

        $response = $this->actingAs($user2)->getJson("/api/transactions/{$transaction->id}");

        $response->assertStatus(422);
    }

    public function test_admin_can_view_all_transaction()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();
        $transaction = Transfer::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($admin)->getJson("/api/transactions/{$transaction->id}");

        $response->assertStatus(200);
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'success',
                'data' => [
                    'transaction_amount' => "{$transaction->amount}",
                    'status' => $transaction->status,
                    'trans_date' => $transaction->created_at->format('c'),
                ]
            ]);
    }
}
