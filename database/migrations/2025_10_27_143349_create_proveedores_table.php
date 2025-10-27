<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            
            // Si tiene RUC es empresa, 11 dígitos
            $table->string('ruc', 11)->nullable()->unique(); 
            
            // Si tiene DNI es persona, 8 dígitos
            $table->string('dni', 8)->nullable()->unique();
            
            $table->text('descripcion')->nullable(); // Descripción opcional
            
            // 1 = Activo, 0 = Inactivo. Por defecto 1 (Activo)
            $table->tinyInteger('estado')->default(1); 
            
            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('proveedores');
    }
};