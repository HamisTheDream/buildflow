<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Check DB Start\n";
    $columns = Schema::getColumnListing('users');
    echo 'Users Columns: ' . implode(', ', $columns) . "\n";

    echo "Migrations Check:\n";
    $migrations = DB::table('migrations')->orderBy('id', 'desc')->limit(10)->get();
    foreach ($migrations as $migration) {
        echo " - " . $migration->migration . " (Batch: " . $migration->batch . ")\n";
    }

    // Check specific migration
    $specific = DB::table('migrations')->where('migration', 'like', '%add_tenant_columns_to_users_table%')->first();
    if ($specific) {
        echo "Found migration: " . $specific->migration . "\n";
    } else {
        echo "Did NOT find migration: 2026_01_27_021328_add_tenant_columns_to_users_table\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
