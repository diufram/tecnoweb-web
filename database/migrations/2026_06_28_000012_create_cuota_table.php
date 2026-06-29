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
        Schema::create('cuota', function (Blueprint $table) {
            $table->id();
            $table->string('estado_cuota');
            $table->date('fecha_vencimiento');
            $table->string('id_transaccion_pago_facil')->nullable();
            $table->decimal('monto', 12, 2);
            $table->integer('nro_cuota');
            $table->foreignId('id_plan_pago')->constrained('plan_pago')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuota');
    }
};
