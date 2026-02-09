<?php
// One-off import script to migrate storage/app/requests.json into DB
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Storage;
use App\Models\InventoryRequest;

echo "Starting import...\n";
$path = 'requests.json';
if (!Storage::exists($path)) {
    echo "No legacy file at storage/app/requests.json\n";
    exit(0);
}

$list = json_decode(Storage::get($path), true) ?: [];
if (count($list) === 0) {
    echo "Legacy file empty.\n";
    exit(0);
}

$imported = 0;
foreach ($list as $l) {
    try {
        $uuid = $l['id'] ?? (string) uniqid('r', true);
        $created = InventoryRequest::firstOrCreate(
            ['uuid' => $uuid],
            [
                'item_id' => $l['item_id'] ?? null,
                'item_name' => $l['item_name'] ?? null,
                'requester' => $l['requester'] ?? null,
                'requester_user_id' => $l['requester_user_id'] ?? null,
                'quantity' => $l['quantity'] ?? 1,
                'role' => $l['role'] ?? null,
                'reason' => $l['reason'] ?? null,
                'return_date' => $l['return_date'] ?? null,
                'status' => $l['status'] ?? 'pending',
                'created_at' => $l['created_at'] ?? now(),
                'updated_at' => $l['updated_at'] ?? now(),
            ]
        );
        if ($created->wasRecentlyCreated) {
            $imported++;
        }
    } catch (\Throwable $e) {
        echo "Failed to import an entry: {$e->getMessage()}\n";
    }
}

echo "Imported {$imported} of " . count($list) . " entries.\n";

return 0;
