<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('auditable');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event', 30);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['auditable_type', 'auditable_id', 'created_at'], 'idx_audit_logs_auditable_time');
            $table->index(['user_id', 'created_at'], 'idx_audit_logs_user_time');
            $table->index(['event', 'created_at'], 'idx_audit_logs_event_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
