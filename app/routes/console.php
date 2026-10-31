<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('academic:create-admin', function () {
    if (\App\Models\User::where('role', 'admin')->exists()) {
        $this->error('Ya existe una cuenta administradora.');
        return 1;
    }
    $name = $this->ask('Nombre');
    $username = $this->ask('Nombre de usuario');
    $email = $this->ask('Correo');
    $password = $this->secret('Contraseña (mínimo 12 caracteres)');
    $email = is_string($email) ? mb_strtolower(trim($email)) : $email;
    $validator = \Illuminate\Support\Facades\Validator::make(compact('name', 'username', 'email', 'password'), [
        'name' => ['required', ...\App\Support\InputRules::personName()],
        'username' => ['required', ...\App\Support\InputRules::username(), 'unique:users,username'],
        'email' => ['required', ...\App\Support\InputRules::email(), 'unique:users,email'],
        'password' => ['required', 'string', 'min:12'],
    ]);
    if ($validator->fails()) {
        foreach ($validator->errors()->all() as $message) $this->error($message);
        return 1;
    }
    \App\Models\User::create(['name' => $name, 'username' => $username, 'email' => $email, 'password' => $password, 'role' => 'admin', 'active' => true]);
    $this->info('Cuenta administradora creada.');
})->purpose('Create the initial administrator interactively');
