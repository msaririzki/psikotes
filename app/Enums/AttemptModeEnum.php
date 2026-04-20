<?php

namespace App\Enums;

enum AttemptModeEnum: string
{
    case MINI_QUIZ = 'mini_quiz';
    case PRACTICE = 'practice';
    case SIMULATION = 'simulation';
}
