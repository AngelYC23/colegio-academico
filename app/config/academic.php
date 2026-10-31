<?php

return [
    'timezone' => env('SCHOOL_TIMEZONE', 'America/Guatemala'),
    'min_score' => (float) env('GRADE_MIN', 0),
    'max_score' => (float) env('GRADE_MAX', 100),
];
