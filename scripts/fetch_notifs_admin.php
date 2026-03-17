<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\InventoryRequest;
use App\Models\InventoryRequestItem;
use App\Models\VehicleMaintenance;
use App\Models\User;
use Carbon\Carbon;

// find an admin user (id=1 fallback)
$admin = User::whereRaw("lower(role) = 'admin'")->where('is_approved', true)->first();
if (!$admin) $admin = User::find(1);
$isAdmin = $admin ? true : false;

try {
    if ($isAdmin) {
        $requestItems = InventoryRequest::where('status', 'pending')->orderBy('created_at','desc')->limit(8)->get();
    } else {
        $requestItems = InventoryRequest::where('requester_user_id', $admin ? $admin->id : 0)
            ->where('status', '!=', 'pending')
            ->orderBy('created_at','desc')
            ->limit(8)->get();
    }

    $mappedRequestItems = $requestItems->map(function($r) use ($isAdmin){
        return [
            'id' => $r->uuid,
            'item_name' => $r->item_name,
            'requester' => $r->requester,
            'subtitle' => 'Requested by ' . ($r->requester ?: 'Unknown'),
            'department' => $r->department ?? null,
            'status' => $r->status,
            'created_at' => $r->created_at ? $r->created_at->toIso8601String() : now()->toIso8601String(),
            'actionable' => $isAdmin,
            'url' => '/requests/' . $r->uuid,
        ];
    });

    $maintenanceItems = collect();
    if ($isAdmin) {
        $maintenanceItems = VehicleMaintenance::with('vehicle')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get()
            ->map(function ($m) {
                $vehicleName = $m->vehicle->name ?? ('Vehicle #' . $m->vehicle_id);
                $plate = $m->vehicle->plate_number ?? 'No plate';
                return [
                    'id' => null,
                    'item_name' => 'Maintenance Request: ' . $vehicleName,
                    'requester' => 'Vehicle Module',
                    'subtitle' => 'Pending review · ' . $plate,
                    'department' => null,
                    'status' => 'pending',
                    'created_at' => $m->created_at ? $m->created_at->toIso8601String() : now()->toIso8601String(),
                    'actionable' => false,
                    'url' => '/vehicle/maintenance?vehicle=' . $m->vehicle_id . '&maintenance=' . $m->id,
                ];
            });
    }

    // overdue items
    $overdueItems = collect();
    try {
        $today = Carbon::now()->toDateString();
        $overdueWindowDays = 30;
        $minDate = Carbon::now()->subDays($overdueWindowDays)->toDateString();

        fwrite(STDERR, "today={$today} minDate={$minDate}\n");
        fwrite(STDERR, "total_items_with_return_date=" . InventoryRequestItem::whereNotNull('return_date')->count() . "\n");
        fwrite(STDERR, "items_return_before_today=" . InventoryRequestItem::whereNotNull('return_date')->whereDate('return_date', '<', $today)->count() . "\n");
        fwrite(STDERR, "items_return_between_min_today=" . InventoryRequestItem::whereNotNull('return_date')->whereDate('return_date', '<', $today)->whereDate('return_date', '>=', $minDate)->count() . "\n");

        $oq = InventoryRequestItem::with('request', 'equipment')
            ->whereNotNull('return_date')
            ->whereDate('return_date', '<', $today)
            ->whereDate('return_date', '>=', $minDate)
                        ->where(function($q){
                                $q->whereIn('status', ['approved','waiting'])
                                    ->orWhere(function($q2){
                                            $q2->where('issued_quantity', '>', 0)
                                                 ->where('status', '!=', 'returned');
                                    });
                        })
            ->orderByDesc('return_date')
            ->limit(8);

        if (!$isAdmin) {
            $oq->whereHas('request', function($q) use ($admin) {
                $q->where('requester_user_id', $admin ? $admin->id : 0);
            });
        }

        $rawOverdue = $oq->get();
        fwrite(STDERR, "rawOverdueCount=" . $rawOverdue->count() . "\n");
        foreach ($rawOverdue as $itDebug) {
            $rDebug = $itDebug->request;
            fwrite(STDERR, "DBG item_id={$itDebug->id} req_id=" . ($rDebug->uuid ?? $rDebug->id ?? 'n/a') . " equip=" . ($itDebug->equipment->name ?? 'n/a') . " status=" . ($itDebug->status ?? 'n/a') . " return_date=" . ($itDebug->return_date ?? 'null') . "\n");
        }

        try {
            $overdueItems = $rawOverdue->map(function($it) use ($isAdmin) {
                $r = $it->request;
                $equipName = $it->equipment->name ?? ($r->item_name ?? 'Item');
                return [
                    'id' => $r->uuid ?? null,
                    'item_name' => $equipName . ' — Overdue',
                    'requester' => $r->requester ?? 'Unknown',
                    'subtitle' => 'Overdue since ' . ($it->return_date ? Carbon::parse($it->return_date)->toDateString() : ''),
                    'department' => $r->department ?? null,
                    'status' => 'overdue',
                    'created_at' => now()->toIso8601String(),
                    // mark overdue entries non-actionable (no approve/reject)
                    'actionable' => false,
                    'url' => '/requests/' . ($r->uuid ?? ''),
                ];
            })->values();
            // deduplicate by request id
            $overdueItems = $overdueItems->unique('id')->values();
        } catch (\Throwable $_mapEx) {
            fwrite(STDERR, "Mapping failed: " . $_mapEx->getMessage() . "\n");
            $overdueItems = collect();
        }

    } catch (\Throwable $_ex) {
        $overdueItems = collect();
    }

    // filter duplicates
    try {
        $existingIds = $mappedRequestItems->pluck('id')->filter()->values()->toArray();
        if (!empty($existingIds) && $overdueItems->count() > 0) {
            $overdueItems = $overdueItems->filter(function($it) use ($existingIds) {
                return empty($it['id']) || !in_array($it['id'], $existingIds);
            })->values();
        }
    } catch (\Throwable $_) {}

    $items = $mappedRequestItems->concat($overdueItems)->concat($maintenanceItems)->sortByDesc('created_at')->take(8)->values();

        // debug counts
        fwrite(STDERR, "mappedRequestItems=" . $mappedRequestItems->count() . "\n");
        fwrite(STDERR, "overdueItems=" . $overdueItems->count() . "\n");
        fwrite(STDERR, "maintenanceItems=" . $maintenanceItems->count() . "\n");

        $out = ['count' => $items->count(), 'items' => $items->values()->toArray()];
        echo json_encode($out, JSON_PRETTY_PRINT);

} catch (\Throwable $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
