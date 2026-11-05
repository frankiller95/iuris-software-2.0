<?php

// Mensajes de validación en español (solo las reglas usadas hasta ahora; ampliar según se necesite).
return [
    'boolean' => 'El campo :attribute debe ser verdadero o falso.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'email' => 'El campo :attribute debe ser un correo válido.',
    'enum' => 'El :attribute seleccionado no es válido.',
    'exists' => 'El :attribute seleccionado no es válido.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'max' => [
        'numeric' => 'El campo :attribute no debe ser mayor que :max.',
        'string' => 'El campo :attribute no debe tener más de :max caracteres.',
    ],
    'min' => [
        'numeric' => 'El campo :attribute debe ser al menos :min.',
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
    ],
    'numeric' => 'El campo :attribute debe ser un número.',
    'password' => [
        'letters' => 'La :attribute debe contener al menos una letra.',
        'mixed' => 'La :attribute debe contener mayúsculas y minúsculas.',
        'numbers' => 'La :attribute debe contener al menos un número.',
        'symbols' => 'La :attribute debe contener al menos un símbolo.',
        'uncompromised' => 'La :attribute aparece en una filtración de datos. Elige otra.',
    ],
    'required' => 'El campo :attribute es obligatorio.',
    'string' => 'El campo :attribute debe ser texto.',
    'unique' => 'El :attribute ya está registrado.',
];
