<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            $table->string('feedback_status')->nullable()->after('mitigation_status'); // null, waiting, responded
            $table->text('feedback_question')->nullable()->after('feedback_status');
            $table->timestamp('feedback_asked_at')->nullable()->after('feedback_question');
            $table->text('feedback_response')->nullable()->after('feedback_asked_at');
            $table->timestamp('feedback_responded_at')->nullable()->after('feedback_response');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            $table->dropColumn([
                'feedback_status',
                'feedback_question',
                'feedback_asked_at',
                'feedback_response',
                'feedback_responded_at',
            ]);
        });
    }
};
