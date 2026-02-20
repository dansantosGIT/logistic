<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Equipment;
use App\Models\InventoryRequest;

class MultiRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_multi_request_creates_parent_and_items()
    {
        // create a user
        $user = User::factory()->create();

        // create equipment: one consumable and one non-consumable
        $consumable = Equipment::create(['name' => 'Tape', 'quantity' => 50, 'type' => 'consumable', 'serial' => 'TAPE-001', 'location' => 'Depot']);
        $nonConsumable = Equipment::create(['name' => 'Radio', 'quantity' => 5, 'type' => 'non-consumable', 'serial' => 'RAD-001', 'location' => 'Depot']);

        $payload = [
            'requester' => 'Test User',
            'role' => 'Employee',
            'department' => 'Ops',
            'items' => [
                [
                    'equipment_id' => $consumable->id,
                    'quantity' => 2,
                    'notes' => 'For field use',
                    'return_date' => null,
                ],
                [
                    'equipment_id' => $nonConsumable->id,
                    'quantity' => 1,
                    'notes' => 'Loan',
                    'return_date' => now()->addDays(7)->toDateString(),
                ],
            ],
        ];

        // surface exceptions for debugging
        $this->withoutExceptionHandling();

        $resp = $this->actingAs($user)->post('/requests/multiple', $payload);
        $resp->assertStatus(302);
        $resp->assertSessionHasNoErrors();

        // verify parent request exists
        $parent = InventoryRequest::where('requester', 'Test User')->first();
        $this->assertNotNull($parent, 'Parent InventoryRequest not created');

        // verify child items
        $this->assertDatabaseHas('inventory_request_items', [
            'inventory_request_id' => $parent->id,
            'equipment_id' => $consumable->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('inventory_request_items', [
            'inventory_request_id' => $parent->id,
            'equipment_id' => $nonConsumable->id,
            'quantity' => 1,
        ]);
    }
}
