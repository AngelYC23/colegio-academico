<?php

use App\Http\Controllers\AccessController;
use App\Http\Controllers\AcademicController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MarksController;
use App\Http\Controllers\CourseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/panel');
Route::middleware('guest')->group(function () {
    Route::view('/ingresar', 'auth.login')->name('login');
    Route::post('/ingresar', [AccessController::class, 'login'])->middleware('throttle:5,1')->name('login.submit');
    Route::view('/recuperar', 'auth.forgot')->name('password.request');
    Route::post('/recuperar', [AccessController::class, 'requestReset'])->middleware('throttle:3,1')->name('password.email');
    Route::get('/restablecer/{token}', fn (string $token, Request $request) => view('auth.reset', ['token' => $token, 'email' => $request->query('email')]))->name('password.reset');
    Route::post('/restablecer', [AccessController::class, 'reset'])->middleware('throttle:3,1')->name('password.update');
});
Route::middleware(['auth', 'active.account'])->group(function () {
    Route::post('/salir', [AccessController::class, 'logout'])->name('logout');
    Route::view('/contrasena', 'auth.change')->name('password.change');
    Route::put('/contrasena', [AccessController::class, 'changePassword'])->name('password.change.save');
    Route::get('/panel', [DashboardController::class, 'home'])->name('dashboard');
    Route::get('/mi-expediente', [DashboardController::class, 'personal'])->name('personal');
    Route::get('/informes/{name}', [DashboardController::class, 'report'])->name('report');
    Route::get('/calificaciones', [MarksController::class, 'index'])->name('marks.index');
    Route::get('/calificaciones/{assignment}', [MarksController::class, 'group'])->name('marks.group');
    Route::post('/calificaciones/{assignment}', [MarksController::class, 'save'])->name('marks.save');
    Route::get('/estudiantes/{student}/cursos', [CourseController::class, 'student'])->whereNumber('student')->name('courses.student');
    Route::put('/estudiantes/{student}/cursos', [CourseController::class, 'saveStudent'])->whereNumber('student')->name('courses.student.save');
    Route::get('/docentes/{teacher}/cursos', [CourseController::class, 'teacher'])->whereNumber('teacher')->name('courses.teacher');
    Route::get('/mis-cursos', [CourseController::class, 'teacher'])->name('courses.mine');
    Route::get('/cursos/{assignment}/estudiantes', [CourseController::class, 'roster'])->whereNumber('assignment')->name('courses.roster');
    Route::get('/gestion/{resource}', [AcademicController::class, 'index'])->name('resource.index');
    Route::get('/gestion/{resource}/nuevo', [AcademicController::class, 'form'])->name('resource.create');
    Route::get('/gestion/{resource}/{id}/editar', [AcademicController::class, 'form'])->name('resource.edit');
    Route::post('/gestion/{resource}', [AcademicController::class, 'save'])->name('resource.store');
    Route::put('/gestion/{resource}/{id}', [AcademicController::class, 'save'])->name('resource.update');
});
