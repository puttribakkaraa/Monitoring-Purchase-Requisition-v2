<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            $table->index('purchasing_group');
            $table->index('req_date');
            $table->index('po_date');
            $table->index('release_indicator');
            $table->index('release_date');
            $table->index(['pr_number', 'item_number']);
        });
    }

    public function down(): void
    {
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            $table->dropIndex(['purchasing_group']);
            $table->dropIndex(['req_date']);
            $table->dropIndex(['po_date']);
            $table->dropIndex(['release_indicator']);
            $table->dropIndex(['release_date']);
            $table->dropIndex(['pr_number', 'item_number']);
        });
    }
};
