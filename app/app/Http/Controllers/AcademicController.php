<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\RelatedLabels;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AcademicController extends Controller
{
    public const RESOURCES = [
        'users' => ['Usuarios', ['name' => 'Nombre', 'username' => 'Usuario', 'email' => 'Correo', 'role' => 'Rol', 'active' => 'Activo']],
        'students' => ['Estudiantes', ['code' => 'Código', 'first_name' => 'Nombres', 'last_name' => 'Apellidos', 'birth_date' => 'Nacimiento', 'sex' => 'Sexo', 'address' => 'Dirección', 'phone' => 'Teléfono', 'email' => 'Correo', 'user_id' => 'Cuenta', 'active' => 'Activo']],
        'teachers' => ['Profesores', ['code' => 'Código', 'first_name' => 'Nombres', 'last_name' => 'Apellidos', 'phone' => 'Teléfono', 'email' => 'Correo', 'address' => 'Dirección', 'user_id' => 'Cuenta', 'active' => 'Activo']],
        'guardians' => ['Encargados', ['first_name' => 'Nombres', 'last_name' => 'Apellidos', 'phone' => 'Teléfono', 'email' => 'Correo', 'address' => 'Dirección', 'user_id' => 'Cuenta', 'active' => 'Activo']],
        'relationships' => ['Parentescos', ['name' => 'Nombre', 'description' => 'Descripción']],
        'student_guardians' => ['Vínculos familiares', ['student_id' => 'Estudiante', 'guardian_id' => 'Encargado', 'relationship_id' => 'Parentesco', 'primary' => 'Principal']],
        'school_years' => ['Ciclos escolares', ['year' => 'Año', 'start_date' => 'Inicio', 'end_date' => 'Fin', 'active' => 'Activo']],
        'grades' => ['Grados', ['name' => 'Nombre', 'level' => 'Nivel', 'description' => 'Descripción', 'active' => 'Activo']],
        'sections' => ['Secciones', ['grade_id' => 'Grado', 'name' => 'Nombre', 'capacity' => 'Capacidad', 'active' => 'Activo']],
        'subjects' => ['Asignaturas', ['code' => 'Código', 'name' => 'Nombre', 'description' => 'Descripción', 'active' => 'Activo']],
        'periods' => ['Períodos', ['school_year_id' => 'Ciclo', 'name' => 'Nombre', 'start_date' => 'Inicio', 'end_date' => 'Fin', 'percentage' => 'Porcentaje', 'open' => 'Abierto']],
        'enrollments' => ['Inscripciones', ['student_id' => 'Estudiante', 'school_year_id' => 'Ciclo', 'grade_id' => 'Grado', 'section_id' => 'Sección', 'enrolled_at' => 'Fecha', 'active' => 'Activo']],
        'assignments' => ['Asignaciones docentes', ['teacher_id' => 'Profesor', 'subject_id' => 'Asignatura', 'school_year_id' => 'Ciclo', 'grade_id' => 'Grado', 'section_id' => 'Sección', 'active' => 'Activo']],
    ];

    private const FOREIGN = ['user_id' => 'users', 'student_id' => 'students', 'guardian_id' => 'guardians', 'relationship_id' => 'relationships', 'school_year_id' => 'school_years', 'grade_id' => 'grades', 'section_id' => 'sections', 'teacher_id' => 'teachers', 'subject_id' => 'subjects'];
    private const OPTIONAL = ['birth_date', 'sex', 'address', 'phone', 'email', 'user_id', 'description'];

    public function index(string $resource)
    {
        $this->admin();
        [$title, $fields] = $this->definition($resource);
        $rows = DB::table($resource)->orderByDesc('id')->paginate(20);
        $related = [];
        foreach (array_keys($fields) as $key) {
            if (isset(self::FOREIGN[$key])) {
                $related[$key] = RelatedLabels::forIds(self::FOREIGN[$key], $rows->getCollection()->pluck($key));
            }
        }
        return view('resource.index', compact('title', 'fields', 'rows', 'resource', 'related'));
    }

    public function form(string $resource, ?int $id = null)
    {
        $this->admin();
        [$title, $fields] = $this->definition($resource);
        $row = $id ? DB::table($resource)->find($id) : null;
        if ($id) abort_unless($row, 404);
        $options = [];
        foreach ($fields as $key => $label) {
            if (isset(self::FOREIGN[$key])) {
                $table = self::FOREIGN[$key];
                $options[$key] = RelatedLabels::options($table);
            }
        }
        return view('resource.form', compact('title', 'fields', 'resource', 'row', 'options'));
    }

    public function save(Request $request, string $resource, ?int $id = null)
    {
        $this->admin();
        [, $fields] = $this->definition($resource);
        if ($id) abort_unless(DB::table($resource)->where('id', $id)->exists(), 404);
        $rules = [];
        foreach ($fields as $key => $label) {
            if (in_array($key, ['active', 'open', 'primary'])) $rules[$key] = ['required', 'boolean'];
            elseif (isset(self::FOREIGN[$key])) $rules[$key] = [in_array($key, self::OPTIONAL) ? 'nullable' : 'required', 'integer', Rule::exists(self::FOREIGN[$key], 'id')];
            elseif (in_array($key, ['start_date', 'end_date', 'enrolled_at', 'birth_date'])) $rules[$key] = [in_array($key, self::OPTIONAL) ? 'nullable' : 'required', 'date'];
            elseif (in_array($key, ['capacity', 'year'])) $rules[$key] = ['required', 'integer', 'min:1', 'max:9999'];
            elseif ($key === 'percentage') $rules[$key] = ['required', 'numeric', 'between:0,100'];
            elseif ($key === 'role') $rules[$key] = ['required', Rule::in(['admin', 'teacher', 'student', 'guardian'])];
            else $rules[$key] = [in_array($key, self::OPTIONAL) ? 'nullable' : 'required', $key === 'email' ? 'email' : 'string', 'max:255'];
        }
        if ($resource === 'users') {
            $rules['email'][0] = 'required';
            $rules['email'][] = Rule::unique('users', 'email')->ignore($id);
            $rules['username'][] = Rule::unique('users', 'username')->ignore($id);
            $rules['password'] = [$id ? 'nullable' : 'required', 'string', 'min:12', 'confirmed'];
        }
        if (in_array($resource, ['students', 'teachers', 'subjects'])) $rules['code'][] = Rule::unique($resource, 'code')->ignore($id);
        if ($resource === 'school_years') $rules['year'][] = Rule::unique('school_years', 'year')->ignore($id);
        if ($resource === 'enrollments') $rules['student_id'][] = Rule::unique('enrollments', 'student_id')->where('school_year_id', $request->input('school_year_id'))->ignore($id);
        if ($resource === 'assignments') $rules['subject_id'][] = Rule::unique('assignments', 'subject_id')->where('school_year_id', $request->input('school_year_id'))->where('section_id', $request->input('section_id'))->ignore($id);
        if ($resource === 'student_guardians') $rules['guardian_id'][] = Rule::unique('student_guardians', 'guardian_id')->where('student_id', $request->input('student_id'))->ignore($id);
        $data = $request->validate($rules);
        if ($resource === 'users' && empty($data['password'])) unset($data['password']);
        if ($resource === 'users' && isset($data['password'])) $data['password'] = Hash::make($data['password']);
        if ($resource === 'users' && $id === Auth::id() && (!$data['active'] || $data['role'] !== 'admin')) throw ValidationException::withMessages(['role' => 'No puede quitar su propio acceso administrativo.']);
        if ($resource === 'users' && $id && $data['role'] !== DB::table('users')->where('id', $id)->value('role')) {
            foreach (['teacher' => 'teachers', 'student' => 'students', 'guardian' => 'guardians'] as $role => $table) {
                if (DB::table($table)->where('user_id', $id)->exists()) throw ValidationException::withMessages(['role' => 'La cuenta está vinculada a un perfil; no puede cambiar su rol.']);
            }
        }
        if (in_array($resource, ['students', 'teachers', 'guardians']) && !empty($data['user_id'])) {
            $expected = ['students' => 'student', 'teachers' => 'teacher', 'guardians' => 'guardian'][$resource];
            if (!DB::table('users')->where('id', $data['user_id'])->where('role', $expected)->exists()) throw ValidationException::withMessages(['user_id' => 'La cuenta debe tener el rol correspondiente.']);
        }
        if (in_array($resource, ['enrollments', 'assignments'])) {
            if (!DB::table('sections')->where('id', $data['section_id'])->where('grade_id', $data['grade_id'])->where('active', true)->exists()) throw ValidationException::withMessages(['section_id' => 'La sección no pertenece a un grado activo.']);
            if (!DB::table('school_years')->where('id', $data['school_year_id'])->where('active', true)->exists()) throw ValidationException::withMessages(['school_year_id' => 'El ciclo no está activo.']);
            if ($id && DB::table('marks')->where($resource === 'enrollments' ? 'enrollment_id' : 'assignment_id', $id)->exists()) {
                $old = DB::table($resource)->find($id);
                foreach (['student_id', 'teacher_id', 'subject_id', 'school_year_id', 'grade_id', 'section_id'] as $key) {
                    if (isset($data[$key]) && $data[$key] != $old->$key) throw ValidationException::withMessages([$key => 'No puede modificar el grupo de un registro con calificaciones.']);
                }
            }
        }
        if (in_array($resource, ['periods', 'school_years']) && $data['end_date'] < $data['start_date']) throw ValidationException::withMessages(['end_date' => 'La fecha final debe ser posterior a la inicial.']);
        if ($resource === 'periods') {
            $cycle = DB::table('school_years')->find($data['school_year_id']);
            if ($data['start_date'] < $cycle->start_date || $data['end_date'] > $cycle->end_date) throw ValidationException::withMessages(['start_date' => 'El período debe estar dentro del ciclo.']);
            if ($id && DB::table('marks')->where('period_id', $id)->exists() && $data['school_year_id'] != DB::table('periods')->where('id', $id)->value('school_year_id')) throw ValidationException::withMessages(['school_year_id' => 'No puede cambiar el ciclo de un período con notas.']);
        }
        DB::transaction(function () use ($resource, $data, $id, $request) {
            if ($resource === 'enrollments') {
                $section = DB::table('sections')->where('id', $data['section_id'])->lockForUpdate()->first();
                if (!$section || !$section->active || $section->grade_id != $data['grade_id']) {
                    throw ValidationException::withMessages(['section_id' => 'La sección no pertenece a un grado activo.']);
                }
                if ($data['active'] && DB::table('enrollments')->where('section_id', $data['section_id'])->where('school_year_id', $data['school_year_id'])->where('active', true)->when($id, fn ($q) => $q->where('id', '<>', $id))->count() >= $section->capacity) {
                    throw ValidationException::withMessages(['section_id' => 'La sección no tiene cupo.']);
                }
            }
            if ($resource === 'sections' && $id) {
                DB::table('sections')->where('id', $id)->lockForUpdate()->first();
                if ($data['capacity'] < DB::table('enrollments')->where('section_id', $id)->where('active', true)->whereIn('school_year_id', DB::table('school_years')->where('active', true)->select('id'))->selectRaw('COUNT(*) as total')->groupBy('school_year_id')->orderByDesc('total')->value('total')) {
                    throw ValidationException::withMessages(['capacity' => 'La capacidad es inferior a las inscripciones vigentes.']);
                }
            }
            $data['updated_at'] = now();
            if ($id) DB::table($resource)->where('id', $id)->update($data);
            else $id = DB::table($resource)->insertGetId($data + ['created_at' => now()]);
            DB::table('audit_logs')->insert(['user_id' => Auth::id(), 'action' => $request->isMethod('put') ? 'update' : 'create', 'entity' => $resource, 'entity_id' => $id, 'ip_address' => $request->ip(), 'changes' => json_encode(collect($data)->except('password')->all()), 'created_at' => now()]);
        });
        return redirect()->route('resource.index', $resource)->with('status', 'Registro guardado correctamente.');
    }

    private function definition(string $resource): array
    {
        abort_unless(isset(self::RESOURCES[$resource]), 404);
        return self::RESOURCES[$resource];
    }

    private function admin(): void
    {
        abort_unless(Auth::user()?->active && Auth::user()?->role === 'admin', 403);
    }
}
