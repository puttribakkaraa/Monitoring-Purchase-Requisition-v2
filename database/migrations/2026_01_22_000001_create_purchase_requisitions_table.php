<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('pr_number')->index(); // Purch.R
            $table->string('requisitioner')->nullable(); // Requisn
            $table->string('item_number')->nullable(); // Item
            $table->text('short_text')->nullable(); // Short Text
            $table->string('po_number')->nullable()->index(); // PO
            $table->string('deletion_flag')->nullable(); // D
            $table->string('gr_indicator')->nullable(); // GR
            $table->string('ir_indicator')->nullable(); // IR
            $table->string('material')->nullable(); // Mater
            $table->string('tracking_number')->nullable(); // Tracking
            $table->string('purchasing_group')->nullable(); // PGr
            $table->string('item_category')->nullable(); // I
            $table->string('account_assignment')->nullable(); // A
            $table->string('release_indicator')->nullable(); // Rel
            $table->string('release_code')->nullable(); // Code
            $table->date('release_date')->nullable(); // Release D
            $table->string('purchasing_org')->nullable(); // POrg
            $table->string('supplier')->nullable(); // Supplier
            $table->string('supplied_material')->nullable(); // Supp. M
            $table->string('rs_status')->nullable(); // RS
            $table->date('req_date')->nullable(); // Req. Date
            $table->decimal('quantity', 15, 2)->nullable(); // Quan
            $table->string('unit')->nullable(); // Un
            $table->date('po_date')->nullable(); // PO Date
            $table->time('po_time')->nullable(); // Time
            $table->string('currency')->nullable(); // Croy
            $table->string('per')->nullable(); // Per
            $table->decimal('total_value', 19, 2)->nullable(); // Tot. value
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_requisitions');
    }
};
