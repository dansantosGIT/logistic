<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$db = $app->make('db');
try {
    $row = $db->select("SHOW COLUMNS FROM account_requests LIKE 'status'")[0] ?? null;
    if ($row) {
        echo "Field: {$row->Field}\nType: {$row->Type}\nNull: {$row->Null}\nDefault: {$row->Default}\nExtra: {$row->Extra}\n";
    } else {
        echo "No status column found or table missing\n";
    }
} catch (\Throwable $e) {
    echo "DB error: " . $e->getMessage() . "\n";
}
