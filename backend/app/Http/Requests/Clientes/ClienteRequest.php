<?php

namespace App\Http\Requests\Clientes;

use App\Models\Cliente;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validación para crear (POST) y actualizar (PUT) clientes. */
class ClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'numero_documento' => is_string($this->numero_documento) ? trim($this->numero_documento) : $this->numero_documento,
            'nombre' => is_string($this->nombre) ? trim(preg_replace('/\s+/', ' ', $this->nombre)) : $this->nombre,
            // El género solo aplica a personas naturales.
            'genero' => $this->tipo_persona === Cliente::JURIDICA ? null : $this->genero,
        ]);
    }

    public function rules(): array
    {
        $clienteId = $this->route('cliente')?->id;

        return [
            'tipo_persona' => ['required', Rule::in(Cliente::TIPOS_PERSONA)],
            'numero_documento' => [
                'required', 'string', 'max:20', 'regex:/^[0-9][0-9.\-]*$/',
                Rule::unique('clientes')->ignore($clienteId),
            ],
            'nombre' => ['required', 'string', 'max:200'],
            'genero' => [Rule::requiredIf($this->tipo_persona === Cliente::NATURAL), 'nullable', Rule::in(Cliente::GENEROS)],
            'municipio_id' => ['required', 'integer', Rule::exists('municipios', 'id')],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'numero_documento.regex' => 'El número de documento solo puede contener números, puntos y guiones.',
            'numero_documento.unique' => 'Ya existe un cliente con ese número de documento.',
            'genero.required' => 'El género es obligatorio para personas naturales.',
        ];
    }

    public function attributes(): array
    {
        return [
            'tipo_persona' => 'tipo de persona',
            'numero_documento' => 'número de documento',
            'nombre' => 'nombre o razón social',
            'genero' => 'género',
            'municipio_id' => 'ciudad',
            'telefono' => 'teléfono',
            'email' => 'correo electrónico',
            'direccion' => 'dirección',
        ];
    }
}
