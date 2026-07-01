<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detalle_compra', function (Blueprint $table) {
            $table->integer('cantidad_contraoferta')->nullable()->after('subtotal');
            $table->decimal('precio_unitario_contraoferta', 12, 2)->nullable()->after('cantidad_contraoferta');
            $table->decimal('subtotal_contraoferta', 12, 2)->nullable()->after('precio_unitario_contraoferta');
        });
    }

    public function down(): void
    {
        Schema::table('detalle_compra', function (Blueprint $table) {
            $table->dropColumn([
                'cantidad_contraoferta',
                'precio_unitario_contraoferta',
                'subtotal_contraoferta',
            ]);
        });
    }
};
