<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('suppliers', 'slug')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->string('slug')->unique()->after('name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('suppliers', 'slug')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->dropUnique(['slug']);
                $table->dropColumn('slug');
            });
        }
    }
};
