<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuentaPorPagar extends Model
{
    use HasFactory;

    protected $table = 'cuentas_por_pagar';

    protected $fillable = [
        'egreso_id',
        'fecha_vencimiento',
        'estado',
        'monto_pagado',
    ];

    /**
     * Relación: Una C/P pertenece a UN Egreso.
     */
    public function egreso()
    {
        return $this->belongsTo(Egreso::class, 'egreso_id');
    }

    /**
     * Relación (Helper): Obtener el Proveedor directamente.
     * Nos saltamos la tabla 'egreso' para llegar a 'proveedor'.
     */
    public function proveedor()
    {
        // Esto es una relación "Has One Through"
        return $this->hasOneThrough(
            Proveedor::class,    // Modelo final al que queremos llegar
            Egreso::class,       // Modelo intermedio
            'id',                // Llave foránea en Egreso (de CuentaPorPagar)
            'id',                // Llave foránea en Proveedor (de Egreso)
            'egreso_id',         // Llave local en CuentaPorPagar
            'proveedor_id'       // Llave local en Egreso
        );
    }
}