<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\RelatedLabels;
use App\Support\InputRules;
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
    private const OPTIONAL = InputRules::OPTIONAL;

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
                if ($key === 'user_id') {
                    $role = ['students' => 'student', 'teachers' => 'teacher', 'guardians' => 'guardian'][$resource];
                    $eligible = DB::table('users')->where('role', $role)
                        ->whereNotIn('id', DB::table($resource)->whereNotNull('user_id')->when($id, fn ($q) => $q->where('id', '<>', $id))->select('user_id'))
                        ->pluck('id')->all();
                    $options[$key] = array_intersect_key($options[$key], array_flip($eligible));
                }
            }
        }
        $defaults = [];
        if (!$id && in_array($resource, ['assignments', 'enrollments'])) {
            $allowed = $resource === 'assignments'
                ? ['teacher_id' => 'teachers', 'school_year_id' => 'school_years', 'grade_id' => 'grades', 'section_id' => 'sections']
                : ['student_id' => 'students'];
            foreach ($allowed as $key => $table) {
                request()->validate([$key => 'nullable|integer|min:1|exists:' . $table . ',id']);
                if (request()->filled($key)) $defaults[$key] = request()->integer($key);
            }
        }
        return view('resource.form', compact('title', 'fields', 'resource', 'row', 'options', 'defaults'));
    }

    public function save(Request $request, string $resource, ?int $id = null)
    {
        $this->admin();
        [, $fields] = $this->definition($resource);
        if ($id) abort_unless(DB::table($resource)->where('id', $id)->exists(), 404);
        InputRules::normalize($request, array_keys($fields));
        $rules = [];
        foreach ($fields as $key => $label) {
            if (in_array($key, ['active', 'open', 'primary'])) $rules[$key] = ['required', 'boolean'];
            elseif (isset(self::FOREIGN[$key])) $rules[$key] = [in_array($key, self::OPTIONAL) ? 'nullable' : 'required', 'integer', 'min:1', Rule::exists(self::FOREIGN[$key], 'id')];
            elseif (in_array($key, ['start_date', 'end_date', 'enrolled_at', 'birth_date'])) $rules[$key] = [in_array($key, self::OPTIONAL) ? 'nullable' : 'required', 'date_format:Y-m-d'];
            elseif (in_array($key, ['capacity', 'year'])) $rules[$key] = ['required', 'integer', 'min:1', 'max:9999'];
            elseif ($key === 'percentage') $rules[$key] = ['required', 'numeric', 'between:0,100'];
            elseif ($key === 'role') $rules[$key] = ['required', Rule::in(['admin', 'teacher', 'student', 'guardian'])];
            else $rules[$key] = [in_array($key, self::OPTIONAL) ? 'nullable' : 'required', 'string', 'max:255', 'not_regex:/[<>\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F\\x7F]/u'];
        }
        foreach (['first_name', 'last_name'] as $key) {
            if (isset($fields[$key])) $rules[$key] = ['required', ...InputRules::personName()];
        }
        if (isset($fields['email'])) $rules['email'] = [$resource === 'users' ? 'required' : 'nullable', ...InputRules::email()];
        if (isset($fields['phone'])) $rules['phone'] = ['nullable', ...InputRules::phone()];
        if (isset($fields['birth_date'])) $rules['birth_date'] = ['nullable', 'date_format:Y-m-d', 'after_or_equal:1900-01-01', 'before_or_equal:' . now(config('academic.timezone'))->toDateString()];
        if (isset($fields['sex'])) $rules['sex'] = ['nullable', 'string', 'max:20', 'regex:/\\A[\\p{L}\\p{M} ]+\\z/u'];
        if (isset($fields['code'])) $rules['code'] = ['required', 'string', 'max:30', 'regex:/\\A[a-zA-Z0-9][a-zA-Z0-9._-]*\\z/'];
        if ($resource === 'school_years') $rules['year'] = ['required', 'integer', 'digits:4', 'min:1900', 'max:9999'];
        if ($resource === 'enrollments') $rules['enrolled_at'][] = 'before_or_equal:' . now(config('academic.timezone'))->toDateString();
        if ($resource === 'users') {
            $rules['name'] = ['required', ...InputRules::personName()];
            $rules['username'] = ['required', ...InputRules::username()];
            $rules['email'][] = Rule::unique('users', 'email')->ignore($id);
            $rules['username'][] = Rule::unique('users', 'username')->ignore($id);
            foreach (['email', 'username'] as $key) {
                $rules[$key][] = function ($attribute, $value, $fail) use ($key, $id) {
                    if (DB::table('users')->whereRaw('LOWER(' . $key . ') = ?', [mb_strtolower($value)])
                        ->when($id, fn ($q) => $q->where('id', '<>', $id))->exists()) {
                        $fail('Ya existe una cuenta con ese ' . ($key === 'email' ? 'correo' : 'usuario') . '.');
                    }
                };
            }
            $rules['password'] = [$id ? 'nullable' : 'required', 'string', 'min:12', 'confirmed'];
            $rules['password_confirmation'] = ['nullable', 'string', 'required_with:password', 'same:password'];
        }
        if (in_array($resource, ['students', 'teachers', 'subjects'])) $rules['code'][] = Rule::unique($resource, 'code')->ignore($id);
        if (in_array($resource, ['students', 'teachers', 'guardians'])) {
            $rules['user_id'][] = Rule::unique($resource, 'user_id')->ignore($id);
        }
        if ($resource === 'relationships') $rules['name'][] = Rule::unique('relationships', 'name')->ignore($id);
        if ($resource === 'grades') $rules['name'][] = Rule::unique('grades', 'name')->where('level', $request->input('level'))->ignore($id);
        if ($resource === 'sections') $rules['name'][] = Rule::unique('sections', 'name')->where('grade_id', $request->input('grade_id'))->ignore($id);
        if ($resource === 'periods') $rules['name'][] = Rule::unique('periods', 'name')->where('school_year_id', $request->input('school_year_id'))->ignore($id);
        if ($resource === 'school_years') $rules['year'][] = Rule::unique('school_years', 'year')->ignore($id);
        if ($resource === 'enrollments') $rules['student_id'][] = Rule::unique('enrollments', 'student_id')->where('school_year_id', $request->input('school_year_id'))->ignore($id);
        if ($resource === 'assignments') $rules['subject_id'][] = Rule::unique('assignments', 'subject_id')->where('school_year_id', $request->input('school_year_id'))->where('section_id', $request->input('section_id'))->ignore($id);
        if ($resource === 'student_guardians') $rules['guardian_id'][] = Rule::unique('student_guardians', 'guardian_id')->where('student_id', $request->input('student_id'))->ignore($id);
        $data = $request->validate($rules, [
            'first_name.regex' => 'Los nombres solo admiten letras, espacios, apóstrofes, puntos y guiones.',
            'last_name.regex' => 'Los apellidos solo admiten letras, espacios, apóstrofes, puntos y guiones.',
            'username.regex' => 'El usuario debe comenzar con una letra o número y solo admite letras, números, puntos, guiones y guiones bajos.',
            'code.regex' => 'El código solo admite letras, números, puntos, guiones y guiones bajos.',
            'sex.regex' => 'Este campo solo admite letras y espacios.',
        ], array_merge($fields, ['password' => 'contraseña']));
        if ($resource === 'users' && $id && strcasecmp((string) DB::table('users')->where('id', $id)->value('email'), $data['email']) !== 0) {
            $data['email_verified_at'] = null;
        }
        unset($data['password_confirmation']);
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
        if ($resource === 'sections' && $id && $data['grade_id'] != DB::table('sections')->where('id', $id)->value('grade_id')) {
            if (DB::table('enrollments')->where('section_id', $id)->exists() || DB::table('assignments')->where('section_id', $id)->exists()) {
                throw ValidationException::withMessages(['grade_id' => 'La sección tiene inscripciones o asignaciones; conserve su grado para mantener el historial.']);
            }
        }
        if (in_array($resource, ['enrollments', 'assignments'])) {
            if (!DB::table('grades')->where('id', $data['grade_id'])->where('active', true)->exists()) {
                throw ValidationException::withMessages(['grade_id' => 'El grado no está activo.']);
            }
            $profile = $resource === 'enrollments' ? 'students' : 'teachers';
            $profileKey = $resource === 'enrollments' ? 'student_id' : 'teacher_id';
            if ($data['active'] && !DB::table($profile)->where('id', $data[$profileKey])->where('active', true)->exists()) {
                throw ValidationException::withMessages([$profileKey => 'Seleccione un perfil activo.']);
            }
            if ($resource === 'assignments' && $data['active'] && !DB::table('subjects')->where('id', $data['subject_id'])->where('active', true)->exists()) {
                throw ValidationException::withMessages(['subject_id' => 'La asignatura no está activa.']);
            }
            if (!DB::table('sections')->where('id', $data['section_id'])->where('grade_id', $data['grade_id'])->where('active', true)->exists()) throw ValidationException::withMessages(['section_id' => 'La sección no pertenece a un grado activo.']);
            if (!DB::table('school_years')->where('id', $data['school_year_id'])->where('active', true)->exists()) throw ValidationException::withMessages(['school_year_id' => 'El ciclo no está activo.']);
            if ($id && DB::table('marks')->where($resource === 'enrollments' ? 'enrollment_id' : 'assignment_id', $id)->exists()) {
                $old = DB::table($resource)->find($id);
                foreach (['student_id', 'teacher_id', 'subject_id', 'school_year_id', 'grade_id', 'section_id'] as $key) {
                    if (isset($data[$key]) && $data[$key] != $old->$key) throw ValidationException::withMessages([$key => 'No puede modificar el grupo de un registro con calificaciones.']);
                }
            }
        }
        if ($resource === 'enrollments') {
            $cycle = DB::table('school_years')->find($data['school_year_id']);
            $birth = DB::table('students')->where('id', $data['student_id'])->value('birth_date');
            if ($data['enrolled_at'] > $cycle->end_date || ($birth && $data['enrolled_at'] <= $birth)) {
                throw ValidationException::withMessages(['enrolled_at' => 'La inscripción debe ser posterior al nacimiento y no superar el fin del ciclo.']);
            }
        }
        if ($resource === 'school_years' && substr($data['start_date'], 0, 4) != $data['year']) {
            throw ValidationException::withMessages(['year' => 'El año del ciclo debe coincidir con el año de su fecha de inicio.']);
        }
        if (in_array($resource, ['periods', 'school_years']) && $data['end_date'] < $data['start_date']) throw ValidationException::withMessages(['end_date' => 'La fecha final debe ser posterior a la inicial.']);
        if ($resource === 'school_years' && $id && DB::table('periods')->where('school_year_id', $id)->where(function ($query) use ($data) {
            $query->where('start_date', '<', $data['start_date'])->orWhere('end_date', '>', $data['end_date']);
        })->exists()) {
            throw ValidationException::withMessages(['start_date' => 'Las fechas del ciclo deben incluir todos sus períodos.']);
        }
        if ($resource === 'periods') {
            $cycle = DB::table('school_years')->find($data['school_year_id']);
            if ($data['start_date'] < $cycle->start_date || $data['end_date'] > $cycle->end_date) throw ValidationException::withMessages(['start_date' => 'El período debe estar dentro del ciclo.']);
            if ($id && DB::table('marks')->where('period_id', $id)->exists() && $data['school_year_id'] != DB::table('periods')->where('id', $id)->value('school_year_id')) throw ValidationException::withMessages(['school_year_id' => 'No puede cambiar el ciclo de un período con notas.']);
        }
        $savedId = DB::transaction(function () use ($resource, $data, $id, $request) {
            if ($id && in_array($resource, ['enrollments', 'assignments'])) {
                $existing = DB::table($resource)->where('id', $id)->lockForUpdate()->first();
                $relationKey = $resource === 'enrollments' ? 'enrollment_id' : 'assignment_id';
                if (DB::table('course_enrollments')->where($relationKey, $id)->exists()) {
                    $protected = $resource === 'enrollments'
                        ? ['student_id', 'school_year_id', 'grade_id', 'section_id']
                        : ['subject_id', 'school_year_id', 'grade_id', 'section_id'];
                    foreach ($protected as $key) {
                        if ($data[$key] != $existing->$key) {
                            throw ValidationException::withMessages([$key => 'El registro tiene cursos o alumnos vinculados; conserve su ciclo, grupo e identidad.']);
                        }
                    }
                }
            }
            if ($resource === 'periods') {
                DB::table('school_years')->where('id', $data['school_year_id'])->lockForUpdate()->first();
                $others = DB::table('periods')->where('school_year_id', $data['school_year_id'])
                    ->when($id, fn ($q) => $q->where('id', '<>', $id));
                if (round((float) (clone $others)->sum('percentage') + (float) $data['percentage'], 2) > 100) {
                    throw ValidationException::withMessages(['percentage' => 'La suma de los porcentajes del ciclo no puede superar el 100 %.']);
                }
                if ((clone $others)->where('start_date', '<=', $data['end_date'])->where('end_date', '>=', $data['start_date'])->exists()) {
                    throw ValidationException::withMessages(['start_date' => 'Las fechas se superponen con otro período del mismo ciclo.']);
                }
            }
            if ($resource === 'student_guardians' && $data['primary']) {
                DB::table('students')->where('id', $data['student_id'])->lockForUpdate()->first();
                if (DB::table('student_guardians')->where('student_id', $data['student_id'])->where('primary', true)
                    ->when($id, fn ($q) => $q->where('id', '<>', $id))->exists()) {
                    throw ValidationException::withMessages(['primary' => 'El estudiante ya tiene un encargado principal.']);
                }
            }
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
            return $id;
        });
        if ($resource === 'enrollments') {
            return redirect()->route('courses.student', ['student' => $data['student_id'], 'enrollment' => $savedId])
                ->with('status', 'Inscripción guardada. Ahora seleccione los cursos del estudiante.');
        }
        if ($resource === 'assignments') {
            return redirect()->route('courses.teacher', $data['teacher_id'])->with('status', 'Curso asignado al profesor. Los alumnos se seleccionan individualmente desde Estudiantes → Cursos.');
        }
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
