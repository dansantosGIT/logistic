<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$email = 'admin@example.com';
$password = 'secret';

$user = User::firstOrCreate(
    ['email' => $email],
    ['name' => 'Admin', 'password' => bcrypt($password)]
);

echo "Admin user ensured: {$user->email} (password: {$password})\n";
