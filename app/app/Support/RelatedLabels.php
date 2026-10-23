<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class RelatedLabels
{
    private const COLUMNS = [
        'users' => ['name', 'username'],
        'students' => ['first_name', 'last_name', 'code'],
        'teachers' => ['first_name', 'last_name', 'code'],
        'guardians' => ['first_name', 'last_name'],
        'relationships' => ['name'],
        'school_years' => ['year'],
        'grades' => ['name', 'level'],
        'sections' => ['name', 'grade_id'],
        'subjects' => ['code', 'name'],
    ];

    public static function forIds(string $table, iterable $ids): array
    {
        $ids = collect($ids)->filter(fn ($id) => $id !== null && $id !== '')->unique()->values();
        if ($ids->isEmpty()) return [];

        $items = DB::table($table)->whereIn('id', $ids)->get(array_merge(['id'], self::COLUMNS[$table]));
        $grades = $table === 'sections'
            ? self::forIds('grades', $items->pluck('grade_id'))
            : [];

        return $items->mapWithKeys(fn ($item) => [
            $item->id => self::label($table, $item, $grades),
        ])->all();
    }

    public static function options(string $table): array
    {
        $items = DB::table($table)->orderBy('id')->get(array_merge(['id'], self::COLUMNS[$table]));
        $grades = $table === 'sections'
            ? self::forIds('grades', $items->pluck('grade_id'))
            : [];

        return $items->mapWithKeys(fn ($item) => [
            $item->id => self::label($table, $item, $grades),
        ])->all();
    }

    private static function label(string $table, object $item, array $grades): string
    {
        return match ($table) {
            'users' => $item->name . ' (' . $item->username . ')',
            'students', 'teachers' => $item->first_name . ' ' . $item->last_name . ' (' . $item->code . ')',
            'guardians' => $item->first_name . ' ' . $item->last_name,
            'relationships' => $item->name,
            'school_years' => (string) $item->year,
            'grades' => $item->name . ' (' . $item->level . ')',
            'sections' => $item->name . ' · ' . ($grades[$item->grade_id] ?? '—'),
            'subjects' => $item->code . ' · ' . $item->name,
        };
    }
}
