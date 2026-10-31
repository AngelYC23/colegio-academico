<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\InputRules;
use App\Rules\AcademicEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AccessController extends Controller
{
    public function login(Request $request)
    {
        $rules = ['identity' => ['required', 'string', 'max:254'], 'password' => 'required|string|max:4096'];
        if (is_string($request->input('identity')) && str_contains($request->input('identity'), '@')) {
            $request->merge(['identity' => mb_strtolower(trim($request->input('identity')))]);
            $rules['identity'][] = new AcademicEmail;
        }
        $data = $request->validate($rules);
        $column = filter_var($data['identity'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        if (!Auth::attempt([$column => $data['identity'], 'password' => $data['password'], 'active' => 1])) {
            throw ValidationException::withMessages(['identity' => 'Las credenciales son incorrectas o la cuenta está desactivada.']);
        }
        if (!Auth::user()->hasActiveOperationalProfile()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            throw ValidationException::withMessages(['identity' => 'Las credenciales son incorrectas o la cuenta o el perfil está desactivado.']);
        }
        $request->session()->regenerate();
        Auth::user()->forceFill(['last_login_at' => now()])->save();
        DB::table('audit_logs')->insert(['user_id' => Auth::id(), 'action' => 'login', 'entity' => 'users', 'entity_id' => Auth::id(), 'ip_address' => $request->ip(), 'created_at' => now()]);
        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate(['current_password' => 'required|current_password', 'password' => 'required|string|min:12|confirmed', 'password_confirmation' => 'required|string|same:password']);
        Auth::user()->update(['password' => Hash::make($data['password'])]);
        DB::table('audit_logs')->insert(['user_id' => Auth::id(), 'action' => 'password_changed', 'entity' => 'users', 'entity_id' => Auth::id(), 'ip_address' => $request->ip(), 'created_at' => now()]);
        return back()->with('status', 'Contraseña actualizada.');
    }

    public function requestReset(Request $request)
    {
        InputRules::normalize($request, ['email']);
        $request->validate(['email' => ['required', ...InputRules::email()]]);
        if (config('mail.default') === 'log') return back()->withErrors(['email' => 'El envío de correo no está configurado. Solicite al administrador que configure un servicio de correo.']);
        Password::sendResetLink($request->only('email'));
        return back()->with('status', 'Si existe la cuenta y está configurado el correo, se enviarán las instrucciones.');
    }

    public function reset(Request $request)
    {
        InputRules::normalize($request, ['email']);
        $data = $request->validate(['token' => 'required|string|max:255', 'email' => ['required', ...InputRules::email()], 'password' => 'required|string|min:12|confirmed', 'password_confirmation' => 'required|string|same:password']);
        $result = Password::reset($data, function (User $user, string $password) {
            $user->forceFill(['password' => Hash::make($password)])->save();
        });
        return $result === Password::PASSWORD_RESET ? redirect()->route('login')->with('status', 'Contraseña restablecida.') : back()->withErrors(['email' => 'No se pudo restablecer la contraseña.']);
    }
}
