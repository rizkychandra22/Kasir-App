<?php

require 'c:/laragon/www/Kasir-App/vendor/autoload.php';
$app = require_once 'c:/laragon/www/Kasir-App/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

foreach (User::all() as $u) {
    echo "ID: {$u->id} | Name: {$u->name} | Username: {$u->username} | Code: {$u->code} | Role: {$u->role}\n";
}
