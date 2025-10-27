<?php

namespace App\Http\Requests\CuentaPorPagar;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Egreso; // Para validar que el egreso exista y tenga proveedor

class StoreCuentaPorPagarRequest extends FormRequest
{

    public function rules()
    {
        return [
            // El egreso_id debe existir en la tabla egresos
            // y NO debe existir ya en la tabla cuentas_por_pagar
            'egreso_id' => [
                'required',
                'integer',
                Rule::exists('egresos', 'id')->where(function ($query) {
                    // Validar adicionalmente que el Egreso tenga un proveedor_id
                    // porque solo esas generan cuentas por pagar
                    $query->whereNotNull('proveedor_id');
                }),
                Rule::unique('cuentas_por_pagar', 'egreso_id') // Único en la tabla de ctas por pagar
            ],
            // La fecha de vencimiento es obligatoria y debe ser una fecha válida
            'fecha_vencimiento' => 'required|date|after_or_equal:today',
        ];
    }

    public function messages()
    {
        return [
            'egreso_id.required' => 'El ID del egreso es obligatorio.',
            'egreso_id.exists' => 'El egreso seleccionado no existe, no tiene proveedor asignado o no es válido.',
            'egreso_id.unique' => 'Este egreso ya tiene una cuenta por pagar registrada.',

            'fecha_vencimiento.required' => 'La fecha de vencimiento es obligatoria.',
            'fecha_vencimiento.date' => 'La fecha de vencimiento no tiene un formato válido.',
            'fecha_vencimiento.after_or_equal' => 'La fecha de vencimiento no puede ser una fecha pasada.',
        ];
    }
}