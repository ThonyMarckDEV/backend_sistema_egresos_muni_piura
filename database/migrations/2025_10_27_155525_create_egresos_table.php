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
        Schema::create('egresos', function (Blueprint $table) {
            $table->id();

            // Monto del egreso (10 dígitos en total, 2 decimales). No puede ser negativo.
            $table->decimal('monto', 10, 2)->unsigned(); 
            
            // Llave foránea para la categoría (obligatoria)
            $table->foreignId('categoria_id')
                  ->constrained('categorias')
                  ->onDelete('restrict'); // Evita borrar categorías si tienen egresos

            // Llave foránea para el proveedor (opcional)
            $table->foreignId('proveedor_id')
                  ->nullable()
                  ->constrained('proveedores')
                  ->onDelete('set null'); // Si se borra el proveedor, el egreso queda (pero sin proveedor)
            
            $table->text('descripcion')->nullable();
            
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
        Schema::dropIfExists('egresos');
    }
};
