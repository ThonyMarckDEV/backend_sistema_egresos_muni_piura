<?php

namespace App\Http\Requests\Contador;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreContadorRequest extends FormRequest
{
    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // Son las mismas reglas que tenías en el controlador.
        return [
            'datos.nombre'          => 'required|string|max:255',
            'datos.apellidoPaterno' => 'required|string|max:255',
            'datos.apellidoMaterno' => 'required|string|max:255',
            'datos.sexo'            => 'required|string|in:M,F',
            'datos.dni'             => 'required|string|digits_between:8,9|unique:datos,dni',
            
            'contacto.telefonoMovil' => 'required|string|unique:contactos,telefonoMovil',
            'contacto.correo'        => 'nullable|email|unique:contactos,correo',
            
            'usuario.username' => 'required|string|max:255|unique:usuarios,username',
            'usuario.password' => 'required|string|min:8',
        ];
    }

    /**
     * Personaliza los mensajes de error para las reglas de validación.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'datos.nombre.required' => 'El nombre es obligatorio.',
            'datos.dni.unique'      => 'El DNI ingresado ya existe.',
            'contacto.telefonoMovil.unique' => 'El teléfono móvil ingresado ya existe.',
            'usuario.username.unique' => 'El nombre de usuario ya está en uso.',
            'usuario.password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ];
    }

    /**
     * Maneja un intento de validación fallido.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        // Esto asegura que la respuesta sea siempre JSON en caso de fallo de API.
        throw new HttpResponseException(response()->json([
            'type' => 'error',
            'message' => 'Error de validación.',
            'errors' => $validator->errors(),
        ], 422));
    }
}