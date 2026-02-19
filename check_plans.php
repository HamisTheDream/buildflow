<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$plans = App\Models\Plan::all();
foreach ($plans as $p) {
    echo "Plan: {$p->key} (ID: {$p->id})\n";
    echo " - Max Projects: {$p->max_projects}\n";
    echo " - Max Members: {$p->max_members}\n";
    echo "-------------------\n";
}
