<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Services\IaeCentralService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery\MockInterface;

class ItemApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock IaeCentralService agar tidak menembak endpoint aslinya saat testing
        $this->mock(IaeCentralService::class, function (MockInterface $mock) {
            $mock->shouldReceive('sendSoapAudit')->andReturn('REC-TEST-123');
            $mock->shouldReceive('publishAmqpEvent')->andReturn(true);
        });
    }

    public function test_user_can_list_items(): void
    {
        Item::factory()->count(3)->create(['status' => 'OPEN']);

        $this->withHeader('X-IAE-KEY', '102022400278')
            ->getJson('/api/v1/items')
            ->assertOk()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => ['id', 'name', 'base_price', 'status']
                ],
                'meta'
            ]);
    }

    public function test_user_gets_404_for_missing_item(): void
    {
        $this->withHeader('X-IAE-KEY', '102022400278')
            ->getJson('/api/v1/items/999')
            ->assertNotFound()
            ->assertJsonStructure([
                'status',
                'message',
                'errors'
            ]);
    }

    public function test_endpoint_requires_api_key(): void
    {
        $this->getJson('/api/v1/items')
            ->assertUnauthorized()
            ->assertJsonStructure([
                'status',
                'message',
                'errors'
            ]);
    }

    public function test_user_can_create_item_with_api_key(): void
    {
        $this->withHeader('X-IAE-KEY', '102022400278')
            ->postJson('/api/v1/items', [
                'name' => 'Kamera Mirrorless',
                'description' => 'Kondisi baik dan siap lelang.',
                'base_price' => 7500000,
                'auction_start_at' => '2026-05-16T10:00:00+07:00',
                'auction_end_at' => '2026-05-20T10:00:00+07:00',
                'status' => 'OPEN',
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Kamera Mirrorless')
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id', 'name', 'receipt_number'
                ]
            ]);
    }
}
