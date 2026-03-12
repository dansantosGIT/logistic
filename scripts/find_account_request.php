<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$db = $app->make('db');
$email = $argv[1] ?? 'toneyquitiquit@gmail.com';
try {
    $rows = $db->select('select id, email, status, name, created_at, updated_at from account_requests where email = ?', [$email]);
    if (empty($rows)) {
        echo "No account_request found for {$email}\n";
    } else {
        foreach ($rows as $r) {
            echo "ID: {$r->id}\nEmail: {$r->email}\nName: {$r->name}\nStatus: {$r->status}\nCreated: {$r->created_at}\nUpdated: {$r->updated_at}\n---\n";
        }
    }
} catch (\Throwable $e) {
    echo "DB error: " . $e->getMessage() . "\n";
}
