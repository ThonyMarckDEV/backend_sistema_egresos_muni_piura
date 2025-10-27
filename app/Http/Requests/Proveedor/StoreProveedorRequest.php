<?php

namespace App\Http\Requests\Proveedor;

use Illuminate\Foundation\Http\FormRequest;

class StoreProveedorRequest extends FormRequest
{
    public function rules()
    {
        return [
            'nombre' => 'required|string|max:255',
            'ruc' => 'nullable|string|size:11|unique:proveedores,ruc',
            'dni' => 'nullable|string|size:8|unique:proveedores,dni',
            'descripcion' => 'nullable|string',
            'estado' => 'required|integer|in:0,1',
        ];
    }

    /**
     * NUEVO: Mensajes de error personalizados.
     */
    public function messages()
    {
        return [
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.max' => 'El nombre no debe exceder los 255 caracteres.',

            'ruc.size' => 'El RUC debe tener 11 dígitos.',
            'ruc.unique' => 'El RUC ingresado ya se encuentra registrado.',

            'dni.size' => 'El DNI debe tener 8 dígitos.',
            'dni.unique' => 'El DNI ingresado ya se encuentra registrado.',

            'estado.required' => 'El campo estado es obligatorio.',
            'estado.in' => 'El estado debe ser 0 (Inactivo) o 1 (Activo).',
        ];
    }
}