<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Egreso extends Model
{
    use HasFactory;

    protected $table = 'egresos';

    /**
     * Atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'monto',
        'categoria_id',
        'proveedor_id',
        'descripcion',
    ];

    /**
     * Casting de atributos (buena práctica para dinero).
     */
    protected $casts = [
        'monto' => 'decimal:2',
    ];

    /**
     * Relación: Un egreso pertenece a una Categoría.
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    /**
     * Relación: Un egreso (opcionalmente) pertenece a un Proveedor.
     */
    public function proveedor()
    {
        // 'withDefault' evita errores si el proveedor es nulo
        return $this->belongsTo(Proveedor::class, 'proveedor_id')->withDefault();
    }
}