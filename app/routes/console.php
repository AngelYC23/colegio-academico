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
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password ?? '') < 12 || !$name || !$username) {
        $this->error('Datos inválidos.');
        return 1;
    }
    \App\Models\User::create(['name' => $name, 'username' => $username, 'email' => $email, 'password' => $password, 'role' => 'admin', 'active' => true]);
    $this->info('Cuenta administradora creada.');
})->purpose('Create the initial administrator interactively');
