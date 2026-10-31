<?php

namespace App\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RecordSearch
{
    private const ACCENTS = ['á' => 'a', 'Á' => 'a', 'é' => 'e', 'É' => 'e', 'í' => 'i', 'Í' => 'i', 'ó' => 'o', 'Ó' => 'o', 'ú' => 'u', 'Ú' => 'u', 'ü' => 'u', 'Ü' => 'u', 'ñ' => 'n', 'Ñ' => 'n'];
    private const RELATED = [
        'users' => ['name', 'username', 'email'], 'students' => ['first_name', 'last_name', 'code'],
        'teachers' => ['first_name', 'last_name', 'code'], 'guardians' => ['first_name', 'last_name'],
        'relationships' => ['name'], 'school_years' => ['year'], 'grades' => ['name', 'level'],
        'sections' => ['name'], 'subjects' => ['name', 'code'],
    ];

    public static function filters(Request $request): array
    {
        $data = $request->validate(['q' => 'nullable|string|max:100', 'cycle' => 'nullable|integer|min:1|exists:school_years,id']);
        if (!mb_check_encoding($data['q'] ?? '', 'UTF-8')) {
            throw \Illuminate\Validation\ValidationException::withMessages(['q' => 'La búsqueda contiene caracteres no válidos.']);
        }
        $search = preg_replace('/\s+/u', ' ', trim($data['q'] ?? ''));
        return [$search, (int) ($data['cycle'] ?? 0)];
    }

    public static function cycles(): Collection
    {
        return DB::table('school_years')->orderByDesc('year')->get(['id', 'year', 'active']);
    }

    /** Every word must match one column or relation. User input is always bound. */
    public static function apply(Builder $query, array $columns, string $search, array $relations = []): void
    {
        if ($search === '') return;
        foreach (preg_split('/\s+/u', $search) as $word) {
            $folded = mb_strtolower(strtr($word, self::ACCENTS));
            $pattern = '%' . strtr($folded, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
            $query->where(function (Builder $any) use ($columns, $relations, $pattern) {
                foreach ($columns as $column) {
                    $any->orWhereRaw(self::foldColumn($column) . " LIKE ? ESCAPE '!'", [$pattern]);
                }
                foreach ($relations as $foreignColumn => $table) {
                    $any->orWhereExists(function (Builder $related) use ($foreignColumn, $table, $pattern) {
                        $related->selectRaw('1')->from($table . ' as search_related')
                            ->whereColumn('search_related.id', $foreignColumn)
                            ->where(function (Builder $fields) use ($table, $pattern) {
                                foreach (self::RELATED[$table] as $column) {
                                    $fields->orWhereRaw(self::foldColumn('search_related.' . $column) . " LIKE ? ESCAPE '!'", [$pattern]);
                                }
                            });
                    });
                }
            });
        }
    }

    private static function foldColumn(string $column): string
    {
        // SQLite has no built-in Unicode case folding. Use portable Spanish folding
        // instead of requiring extensions or interpolating search text into SQL.
        $wrapped = DB::connection()->getQueryGrammar()->wrap($column);
        $cast = in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb']) ? 'CHAR' : 'TEXT';
        $expression = "LOWER(COALESCE(CAST($wrapped AS $cast), ''))";
        foreach (self::ACCENTS as $from => $to) {
            $expression = "REPLACE($expression, '$from', '$to')";
        }
        return $expression;
    }
}
