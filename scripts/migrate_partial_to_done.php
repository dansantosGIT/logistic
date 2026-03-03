<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\InventoryRequest;
use Illuminate\Support\Facades\Log;

echo "Scanning for requests with status 'partial'...\n";
$items = InventoryRequest::where('status', 'partial')->with('items.equipment')->get();
$count = $items->count();
echo "Found {$count} partial request(s).\n";
if ($count === 0) {
    exit(0);
}

$confirmed = in_array('--yes', $argv ?? []);
if (!$confirmed) {
    echo "This will update matching requests to 'done' or other final statuses based on child items. Type YES to proceed: ";
    $line = trim(fgets(STDIN));
    if ($line !== 'YES') { echo "Aborted. No changes made.\n"; exit(1); }
}

$updated = 0;
foreach ($items as $r) {
    try {
        $total = $r->items()->count();
        $approved = $r->items()->where('status', 'approved')->count();
        $rejected = $r->items()->where('status', 'rejected')->count();
        $pending = max(0, $total - $approved - $rejected);

        $waitingCount = $r->items()
            ->where('status', 'approved')
            ->whereHas('equipment', function($q){
                $q->whereRaw('LOWER(COALESCE(`type`, "")) <> ?', ['consumable']);
            })->count();

        $orig = $r->status;
        if ($waitingCount > 0) {
            $r->status = 'waiting';
        } elseif ($pending > 0) {
            // still undecided — skip conversion
            echo "Skipping request {$r->id} (pending child items).\n";
            continue;
        } else {
            if ($approved === $total && $total > 0) {
                $r->status = 'approved';
            } elseif ($rejected === $total) {
                $r->status = 'rejected';
            } elseif ($approved > 0) {
                $r->status = 'done';
            } else {
                $r->status = 'done';
            }
        }
        $r->handled_by = $r->handled_by ?? null;
        $r->updated_at = now();
        $r->save();
        echo "Updated request {$r->id} ({$r->uuid}): {$orig} -> {$r->status}\n";
        $updated++;
    } catch (Throwable $e) {
        echo "Error updating request {$r->id}: " . $e->getMessage() . "\n";
    }
}

echo "Done. {$updated} request(s) updated.\n";
exit(0);
