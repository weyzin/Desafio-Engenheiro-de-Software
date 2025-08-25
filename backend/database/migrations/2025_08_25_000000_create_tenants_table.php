<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 120);
            $table->string('slug', 60)->unique(); // usado no subdomínio e X-Tenant
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('tenants');
    }
};
