<?php

namespace App\Http\Requests\CuentaPorPagar;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PagarCuentaRequest extends FormRequest
{

    public function rules()
    {
        // El método 'efectivo' es un caso especial
        $metodoPago = $this->input('metodo_pago');

        return [
            'metodo_pago' => [
                'required',
                'string',
                Rule::in(['Efectivo', 'Deposito', 'Yape', 'Plin', 'Transferencia']), // Ajusta según necesites
            ],
            // El número de operación es requerido A MENOS QUE sea 'Efectivo'
            'numero_operacion' => [
                Rule::requiredIf(function () use ($metodoPago) {
                    return $metodoPago !== 'Efectivo';
                }),
                'nullable', // Permite que sea null si es 'Efectivo'
                'string',
                'max:255',
            ],
        ];
    }

    public function messages()
    {
        return [
            'metodo_pago.required' => 'Debe seleccionar un método de pago.',
            'metodo_pago.in' => 'El método de pago seleccionado no es válido.',
            'numero_operacion.required' => 'El número de operación es obligatorio para este método de pago.',
        ];
    }
}