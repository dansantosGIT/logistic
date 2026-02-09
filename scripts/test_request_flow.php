<?php
// Test script: create users, create request, admin approves, show results
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\InventoryRequest;

echo "Starting test flow...\n";

// create or find users
$tester = User::firstOrCreate(
    ['email' => 'tester@example.com'],
    ['name' => 'tester', 'password' => Hash::make('secret')]
);
$admin = User::firstOrCreate(
    ['email' => 'admin@example.com'],
    ['name' => 'admin', 'password' => Hash::make('secret')]
);

echo "Tester id: {$tester->id}, Admin id: {$admin->id}\n";

// create a pending request as tester
$req = InventoryRequest::create([
    'uuid' => (string) uniqid('r', true),
    'item_id' => 1,
    'item_name' => 'Test Item',
    'requester' => 'Tester Person',
    'requester_user_id' => $tester->id,
    'quantity' => 1,
    'role' => 'Staff',
    'reason' => 'Testing approval flow',
    'return_date' => null,
    'status' => 'pending',
]);

echo "Created request uuid: {$req->uuid}\n";

// simulate admin fetching notifications
Auth::login($admin);
$pending = InventoryRequest::where('status', 'pending')->get();
echo "Admin sees {".count($pending)."} pending request(s)\n";
foreach ($pending as $p) {
    echo " - {$p->uuid} : {$p->item_name} (requester_user_id={$p->requester_user_id})\n";
}

// approve the created request
$r = InventoryRequest::where('uuid', $req->uuid)->first();
if ($r) {
    $r->status = 'approved';
    $r->handled_by = $admin->id;
    $r->save();
    echo "Request {$r->uuid} approved by admin (id={$admin->id})\n";
} else {
    echo "Could not find created request to approve.\n";
}

// simulate tester seeing update
Auth::login($tester);
$updates = InventoryRequest::where('requester_user_id', $tester->id)
    ->where('status', '!=', 'pending')
    ->get();
echo "Tester sees {".count($updates)."} update(s)\n";
foreach ($updates as $u) {
    echo " - {$u->uuid} : status={$u->status}, handled_by={$u->handled_by}\n";
}

echo "Test flow complete.\n";

return 0;
