<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\InventoryRequest;
use App\Models\InventoryRequestItem;
use Illuminate\Support\Facades\Storage;

// Count existing records
$reqCount = InventoryRequest::count();
$itemCount = InventoryRequestItem::count();

echo "Found {$reqCount} requests and {$itemCount} request items.\n";

// Backup current requests to storage/app/requests_backup_TIMESTAMP.json
$timestamp = date('Ymd_His');
$backupPath = storage_path('app/requests_backup_' . $timestamp . '.json');
$data = InventoryRequest::with(['items'])->get()->toArray();
file_put_contents($backupPath, json_encode($data, JSON_PRETTY_PRINT));
echo "Backup written to: {$backupPath}\n";

// Confirm unless --yes provided
$confirmed = in_array('--yes', $argv ?? []);
if (!$confirmed) {
    echo "Type YES to permanently delete these records: ";
    $line = trim(fgets(STDIN));
    if ($line !== 'YES') {
        echo "Aborted. No changes made.\n";
        exit(1);
    }
}

// Perform deletion inside a transaction
try {
    \DB::transaction(function() use ($reqCount, $itemCount) {
        InventoryRequestItem::query()->delete();
        InventoryRequest::query()->delete();
    });
    echo "Deleted requests and request items.\n";
    echo "You can restore from backup file if needed: {$backupPath}\n";
} catch (Throwable $e) {
    echo "Error during deletion: " . $e->getMessage() . "\n";
    exit(2);
}

return 0;
