<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['property_reviews', 'agent_reviews', 'company_reviews'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->text('admin_notes')->nullable()->after('status');
                $table->timestamp('reviewed_at')->nullable()->after('admin_notes');
                $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')
                    ->constrained('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (['property_reviews', 'agent_reviews', 'company_reviews'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropConstrainedForeignId('reviewed_by');
                $table->dropColumn(['admin_notes', 'reviewed_at']);
            });
        }
    }
};
