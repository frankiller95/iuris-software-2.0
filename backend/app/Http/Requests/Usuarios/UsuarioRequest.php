<?php

namespace App\Http\Requests\Usuarios;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** Validación para crear (POST) y actualizar (PUT) usuarios. */
class UsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // El acceso lo controla el middleware "admin".
    }

    public function rules(): array
    {
        $userId = $this->route('usuario')?->id;
        $creating = $this->isMethod('post');

        return [
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'numero_documento' => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($userId)],
            'email' => ['required', 'email', 'max:150', Rule::unique('users')->ignore($userId)],
            'telefono' => ['nullable', 'string', 'max:20'],
            'rol_id' => ['required', 'integer', Rule::exists('roles', 'id')],
            'activo' => ['sometimes', 'boolean'],
            'password' => [$creating ? 'required' : 'nullable', 'confirmed', Password::min(8)],
        ];
    }

    public function attributes(): array
    {
        return [
            'nombres' => 'nombres',
            'apellidos' => 'apellidos',
            'numero_documento' => 'número de documento',
            'email' => 'correo electrónico',
            'telefono' => 'teléfono',
            'rol_id' => 'rol',
            'password' => 'contraseña',
        ];
    }
}
