<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('npk')->unique()->nullable()->after('id');
            $table->string('role')->default('admin')->after('email');
        });

        // Seed default admin
        \App\Models\User::where('id', 1)->update([
            'npk' => '1234',
            'name' => 'Admin',
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['npk', 'role']);
        });
    }
};
