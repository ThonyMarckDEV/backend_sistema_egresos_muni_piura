<?php

namespace App\Http\Requests\Contador;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use App\Models\Contador;

class UpdateContadorRequest extends FormRequest
{
    /**
     * Obtiene las reglas de validación.
     */
    public function rules(): array
    {
        // 1. Obtenemos el ID del contador desde la ruta 
        $contadorId = $this->route('id'); 
        
        // 2. Buscamos el contador y los IDs de sus tablas relacionadas
        try {
            $contador = Contador::with('datos.contacto')->findOrFail($contadorId);
            $datosId = $contador->datos->id;
            // Verificamos si el contacto existe antes de tomar su ID
            $contactoId = $contador->datos->contacto ? $contador->datos->contacto->id : null;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Si no se encuentra, la validación fallará de todos modos en el controlador
            // Devolvemos reglas básicas para evitar un error 500 aquí.
            return ['usuario.username' => 'required'];
        }

        // 3. Definimos las reglas
        return [
            'datos.nombre'          => 'required|string|max:255',
            'datos.apellidoPaterno' => 'required|string|max:255',
            'datos.apellidoMaterno' => 'required|string|max:255',
            'datos.sexo'            => 'required|string|in:M,F',
            'datos.dni'             => [
                'required', 'string', 'digits_between:8,9',
                // Ignora este 'datosId' al verificar si el DNI es único
                Rule::unique('datos', 'dni')->ignore($datosId),
            ],
            
            'contacto.telefonoMovil' => [
                'required', 'string',
                Rule::unique('contactos', 'telefonoMovil')->ignore($contactoId),
            ],
            'contacto.correo'        => [
                'nullable', 'email',
                Rule::unique('contactos', 'correo')->ignore($contactoId),
            ],
            
            'usuario.username' => [
                'required', 'string', 'max:255',
                // Ignora este 'contadorId' (que es un ID de 'usuarios')
                Rule::unique('usuarios', 'username')->ignore($contadorId),
            ],
            
            // La contraseña es opcional en la actualización
            'usuario.password' => 'nullable|string|min:8', 
            
            // El estado sí es requerido
            'usuario.estado'   => 'required|integer|in:0,1',
        ];
    }

    /**
     * Maneja un intento de validación fallido.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'type' => 'error',
            'message' => 'Error de validación.',
            'errors' => $validator->errors(), // Usamos 'errors'
        ], 422));
    }
}