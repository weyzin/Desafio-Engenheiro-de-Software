<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->uuid('tenant_id'); // escopo global
            $table->string('brand', 80);
            $table->string('model', 80);
            $table->unsignedSmallInteger('year');
            $table->decimal('price', 12, 2);
            $table->enum('status', ['available','reserved','sold'])->default('available');
            $table->json('images_json')->nullable();

            // auditoria
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // índices úteis p/ filtros
            $table->index(['tenant_id', 'brand']);
            $table->index(['tenant_id', 'model']);
            $table->index(['tenant_id', 'price']);
            $table->index(['tenant_id', 'year']);

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    public function down(): void {
        Schema::dropIfExists('vehicles');
    }
};
