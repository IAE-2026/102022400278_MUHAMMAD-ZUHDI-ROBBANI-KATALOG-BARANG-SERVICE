<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_items(): void
    {
        Item::factory()->count(3)->create(['status' => 'OPEN']);

        $this->getJson('/api/items')
            ->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_user_gets_404_for_missing_item(): void
    {
        $this->getJson('/api/items/999')
            ->assertNotFound();
    }

    public function test_admin_endpoint_requires_api_key(): void
    {
        $this->postJson('/api/admin/items', [])
            ->assertUnauthorized();
    }

    public function test_admin_can_create_item_with_api_key(): void
    {
        ApiKey::query()->create([
            'name' => 'Test Admin',
            'key_hash' => hash('sha256', 'test-admin-key'),
            'abilities' => ['admin'],
        ]);

        $this->withHeader('Authorization', 'Bearer test-admin-key')
            ->postJson('/api/admin/items', [
                'name' => 'Kamera Mirrorless',
                'description' => 'Kondisi baik dan siap lelang.',
                'base_price' => 7500000,
                'auction_start_at' => '2026-05-16T10:00:00+07:00',
                'auction_end_at' => '2026-05-20T10:00:00+07:00',
                'status' => 'OPEN',
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Kamera Mirrorless');
    }
}
