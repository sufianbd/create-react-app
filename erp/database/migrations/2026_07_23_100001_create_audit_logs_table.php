<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('action')->nullable()->after('event');
            $table->string('auditable_label')->nullable()->after('auditable_id');
            $table->string('url')->nullable()->after('user_agent');
            $table->string('module')->nullable()->after('url');
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropColumn(['action', 'auditable_label', 'url', 'module']);
        });
    }
};
