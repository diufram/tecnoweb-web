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
        Schema::create('venta', function (Blueprint $table) {
            $table->id();
            $table->string('estado_venta');
            $table->date('fecha');
            $table->decimal('total', 12, 2);
            $table->foreignId('id_cliente')->constrained('cliente', 'id_usuario')->cascadeOnDelete();
            $table->foreignId('id_propietario')->constrained('propietario', 'id_usuario')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venta');
    }
};
