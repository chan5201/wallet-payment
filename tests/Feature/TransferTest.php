<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class TransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_initiate_transfer()
    {
        $sender = User::factory()->create(['balance' => 1000]);
        $receiver = User::factory()->create();

        $response = $this->actingAs($sender)->postJson('/api/transfers', [
            'receiver_id' => $receiver->email,
            'amount' => 500,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id'
                ]
            ]);
    }

    public function test_transfer_fails_if_insufficient_balance()
    {
        $sender = User::factory()->create(['balance' => 100]);
        $receiver = User::factory()->create();

        $response = $this->actingAs($sender)->postJson('/api/transfers', [
            'receiver_id' => $receiver->email,
            'amount' => 500,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => false,
                'message' => 'Insufficient balance',
                'data' => []
            ]);
    }

    public function test_transfer_to_sender()
    {
        $sender = User::factory()->create(['balance' => 100]);
        $receiver = $sender;

        $response = $this->actingAs($sender)->postJson('/api/transfers', [
            'receiver_id' => $receiver->email,
            'amount' => 500,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => false,
                'message' => 'Invalid receiver',
                'data' => []
            ]);
    }
}
