<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contador extends User
{
    use HasFactory;

    /**
     * El rol específico para los contadores.
     */
    protected const ID_ROL_CONTADOR = 2;

    /**
     * La "llave" de la tabla que debería ser "booted".
     */
    protected static function booted(): void
    {
        // Asigna automáticamente id_Rol = 2 al crear un nuevo Contador
        static::creating(function (Contador $contador) {
            $contador->id_Rol = self::ID_ROL_CONTADOR;
        });

        // Agrega un scope global para que CUALQUIER consulta a este modelo
        // solo traiga usuarios con id_Rol = 2.
        static::addGlobalScope('contador', function (Builder $builder) {
            $builder->where('id_Rol', self::ID_ROL_CONTADOR);
        });
    }
}