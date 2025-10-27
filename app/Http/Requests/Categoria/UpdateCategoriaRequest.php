<?php

namespace App\Http\Requests\Categoria;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateCategoriaRequest extends FormRequest
{
    /**
     * Obtiene las reglas de validación.
     */
    public function rules(): array
    {
        // Obtenemos el ID de la categoría desde la ruta
        $categoriaId = $this->route('id');

        return [
            'nombre' => [
                'required', 'string', 'max:255',
                // El nombre debe ser único, ignorando el registro actual
                Rule::unique('categorias', 'nombre')->ignore($categoriaId),
            ],
            'estado' => 'required|boolean', // El estado es requerido al actualizar
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
            'estado.required' => 'El estado es obligatorio.',
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