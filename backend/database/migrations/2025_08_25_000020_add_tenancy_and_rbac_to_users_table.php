<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            // multitenancy
            $table->uuid('tenant_id')->nullable()->after('id');
            $table->index('tenant_id');

            // RBAC
            $table->string('role', 20)->default('agent')->after('email'); // superuser/owner/agent
            $table->index('role');

            // auditoria / segurança
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
            $table->timestamp('last_password_change_at')->nullable()->after('last_login_at');
            $table->unsignedBigInteger('created_by')->nullable()->after('updated_at');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            $table->unsignedBigInteger('deleted_by')->nullable()->after('updated_by');

            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
        });
    }

    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn([
                'tenant_id','role','last_login_at','last_password_change_at',
                'created_by','updated_by','deleted_by',
            ]);
        });
    }
};
