<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cuentas_por_pagar', function (Blueprint $table) {
            $table->id();

            // 1. El Vínculo al Egreso (debe ser único)
            $table->foreignId('egreso_id')
                  ->unique() // Un egreso solo genera una cuenta por pagar
                  ->constrained('egresos')
                  ->onDelete('cascade'); // Si se borra el egreso, se borra la cuenta

            // 2. La fecha de vencimiento (OBLIGATORIA)
            $table->date('fecha_vencimiento');

            // 3. El Estado (como pide la validación)
            $table->string('estado', 20)->default('pendiente'); // (pendiente, pagado, vencido)

            // 4. Para pagos parciales (opcional pero recomendado)
            $table->decimal('monto_pagado', 10, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cuentas_por_pagar');
    }
};
