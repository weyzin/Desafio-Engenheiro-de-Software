<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tenant_domains', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->string('domain', 255)->unique(); // ex.: acme.local.test ou custom domain
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }
    public function down(): void {
        Schema::dropIfExists('tenant_domains');
    }
};
