<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('version', 120)->nullable()->after('model');
            $table->unsignedInteger('km')->default(0)->after('year');
            $table->string('notes', 1000)->nullable()->after('status');

            // índices úteis (opcional)
            $table->index(['tenant_id', 'km']);
            $table->index(['tenant_id', 'version']);
        });
    }

    public function down(): void {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropIndex(['vehicles_tenant_id_km_index']);
            $table->dropIndex(['vehicles_tenant_id_version_index']);
            $table->dropColumn(['version','km','notes']);
        });
    }
};
