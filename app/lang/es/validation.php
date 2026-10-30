<?php
return [
    'required' => 'El campo :attribute es obligatorio.',
    'email' => 'El campo :attribute debe ser un correo válido.',
    'unique' => 'Ya existe un registro con ese valor de :attribute.',
    'exists' => 'La opción seleccionada para :attribute no está disponible.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'current_password' => 'La contraseña actual es incorrecta.',
    'string' => 'El campo :attribute debe ser texto.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'numeric' => 'El campo :attribute debe ser un número.',
    'boolean' => 'Seleccione una opción válida para :attribute.',
    'date' => 'El campo :attribute debe ser una fecha válida.',
    'in' => 'La opción seleccionada para :attribute no es válida.',
    'min' => ['string' => 'El campo :attribute debe tener al menos :min caracteres.', 'numeric' => 'El campo :attribute debe ser al menos :min.'],
    'max' => ['string' => 'El campo :attribute no debe superar :max caracteres.', 'numeric' => 'El campo :attribute no debe superar :max.'],
    'between' => ['numeric' => 'El campo :attribute debe estar entre :min y :max.'],
    'attributes' => ['identity' => 'usuario o correo', 'password' => 'contraseña', 'email' => 'correo', 'current_password' => 'contraseña actual', 'score' => 'nota', 'observation' => 'observación', 'period_id' => 'período', 'enrollment_id' => 'inscripción'],
];
