<?php

namespace App\Http\Requests\Categoria;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCategoriaRequest extends FormRequest
{

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     */
    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255|unique:categorias,nombre',
            'estado' => 'nullable|boolean', // Opcional, por defecto es 1 (Activo)
        ];
    }

    /**
     * Personaliza los mensajes de error.
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.unique'   => 'El nombre de la categoría ya existe.',
        ];
    }

    /**
     * Maneja un intento de validación fallido.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'type'    => 'error',
            'message' => 'Error de validación.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}