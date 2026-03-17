<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;

try {
    Mail::raw('Test email from application', function ($m) {
        $m->to(env('MAIL_ADMIN'))->subject('Test email from app');
    });
    echo "Mail send attempted\n";
} catch (\Exception $e) {
    echo "Mail send failed: " . $e->getMessage() . "\n";
    // also dump full trace for debugging
    echo $e->getTraceAsString() . "\n";
}
