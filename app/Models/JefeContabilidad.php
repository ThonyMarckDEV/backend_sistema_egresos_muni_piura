<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JefeContabilidad extends User
{
    use HasFactory;

    /**
     * El rol específico para los jefes de contabilidad.
     */
    protected const ID_ROL_JEFE_CONTABILIDAD = 3;

    /**
     * La "llave" de la tabla que debería ser "booted".
     */
    protected static function booted(): void
    {
        // Asigna automáticamente id_Rol = 3 al crear un nuevo JefeContabilidad
        static::creating(function (JefeContabilidad $jefeContabilidad) {
            $jefeContabilidad->id_Rol = self::ID_ROL_JEFE_CONTABILIDAD;
        });

        // Agrega un scope global para que CUALQUIER consulta a este modelo
        // solo traiga usuarios con id_Rol = 3.
        static::addGlobalScope('jefe_contabilidad', function (Builder $builder) {
            $builder->where('id_Rol', self::ID_ROL_JEFE_CONTABILIDAD);
        });
    }
}