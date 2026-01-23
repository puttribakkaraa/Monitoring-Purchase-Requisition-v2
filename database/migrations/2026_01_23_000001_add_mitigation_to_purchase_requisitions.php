<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            $table->text('mitigation_reason')->nullable();
            $table->string('mitigation_status')->nullable()->default('open'); // open, in_progress, resolved
        });
    }

    public function down(): void
    {
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            $table->dropColumn(['mitigation_reason', 'mitigation_status']);
        });
    }
};
