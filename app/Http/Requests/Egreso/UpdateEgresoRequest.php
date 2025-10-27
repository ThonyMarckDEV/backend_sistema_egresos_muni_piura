<?php

namespace App\Http\Requests\Egreso;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEgresoRequest extends FormRequest
{

    public function rules()
    {
        // Usamos 'sometimes' para permitir actualizaciones parciales (PATCH)
        // Si usas PUT, cambia 'sometimes' por 'required'
        return [
            'monto' => 'sometimes|required|numeric|min:0.01',
            'categoria_id' => 'sometimes|required|integer|exists:categorias,id',
            'proveedor_id' => 'nullable|integer|exists:proveedores,id',
            'descripcion' => 'nullable|string|max:1000',
        ];
    }
    
    // Puedes reusar los mismos mensajes que en StoreEgresoRequest
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