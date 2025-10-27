<?php

namespace App\Http\Requests\Egreso;

use Illuminate\Foundation\Http\FormRequest;

class StoreEgresoRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Asumimos que la autorización se maneja en otro lugar
    }

    public function rules()
    {
        return [
            'monto' => 'required|numeric|min:0.01',
            'categoria_id' => 'required|integer|exists:categorias,id', // Debería existir en la tabla categorias
            'proveedor_id' => 'nullable|integer|exists:proveedores,id', // Si se envía, debe existir
            'descripcion' => 'nullable|string|max:1000',
        ];
    }

    public function messages()
    {
        return [
            'monto.required' => 'El monto es obligatorio.',
            'monto.numeric' => 'El monto debe ser un número.',
            'monto.min' => 'El monto debe ser al menos 0.01.',
            
            'categoria_id.required' => 'La categoría es obligatoria.',
            'categoria_id.exists' => 'La categoría seleccionada no es válida.',
            
            'proveedor_id.exists' => 'El proveedor seleccionado no es válido.',

            'descripcion.max' => 'La descripción no debe exceder los 1000 caracteres.',
        ];
    }
}