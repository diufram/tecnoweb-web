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
        Schema::create('compra', function (Blueprint $table) {
            $table->id();
            $table->string('estado');
            $table->date('fecha_emision');
            $table->decimal('monto_total', 12, 2);
            $table->text('observaciones');
            $table->foreignId('id_propietario')->constrained('propietario', 'id_usuario')->cascadeOnDelete();
            $table->foreignId('id_proveedor')->constrained('proveedor', 'id_usuario')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compra');
    }
};
