<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mailer = $app->make('mailer');

$to = 'sjcdrrmdlogistics@gmail.com';

try {
    $mailer->raw('SMTP test from Logistic', function ($m) use ($to) {
        $m->to($to)->subject('SMTP test');
    });
    echo "Test mail attempted to send to {$to}\n";
} catch (\Throwable $e) {
    echo "Mail send failed: " . $e->getMessage() . "\n";
}
