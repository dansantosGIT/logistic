<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\InventoryRequestItem;
use Carbon\Carbon;

$today = Carbon::now()->toDateString();
$minDate = Carbon::now()->subDays(30)->toDateString();

$items = InventoryRequestItem::with('request','equipment')
    ->whereNotNull('return_date')
    ->whereDate('return_date', '<', $today)
    ->whereDate('return_date', '>=', $minDate)
    ->where(function($q){
        $q->whereIn('status', ['approved','waiting'])
          ->orWhere('issued_quantity', '>', 0);
    })
    ->orderByDesc('return_date')
    ->get();

echo "Found: " . count($items) . " overdue items\n";
foreach ($items as $it) {
    $req = $it->request;
    $equip = $it->equipment;
    echo "item_id={$it->id} request_id=" . ($req->uuid ?? $req->id ?? 'n/a') . " equipment=" . ($equip->name ?? $it->item_name ?? 'n/a') . " return_date=" . ($it->return_date ?? 'null') . " status=" . ($it->status ?? 'n/a') . " issued_quantity=" . intval($it->issued_quantity) . PHP_EOL;
}
