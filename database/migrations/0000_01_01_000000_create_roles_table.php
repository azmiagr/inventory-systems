<?php

use App\Constants\RoleConstants;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        $now = now();
        DB::table('roles')->insert([
            ['id' => RoleConstants::ADMIN, 'name' => 'Admin', 'description' => 'Full access ke seluruh sistem', 'created_at' => $now, 'updated_at' => $now],
            ['id' => RoleConstants::STAFF, 'name' => 'Staff', 'description' => 'Bisa input transaksi dan lihat laporan', 'created_at' => $now, 'updated_at' => $now],
            ['id' => RoleConstants::VIEWER, 'name' => 'Viewer', 'description' => 'Hanya bisa melihat data (read-only)', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
