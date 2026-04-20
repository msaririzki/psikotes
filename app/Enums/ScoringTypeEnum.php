<?php

namespace App\Enums;

enum ScoringTypeEnum: string
{
    case OBJECTIVE = 'objective';
    case WEIGHTED = 'weighted';
    case SPEED_ACCURACY = 'speed_accuracy';
    case PERSONALITY_PROFILE = 'personality_profile';

    public function label(): string
    {
        return match ($this) {
            self::OBJECTIVE => 'Objektif',
            self::WEIGHTED => 'Berbobot',
            self::SPEED_ACCURACY => 'Kecepatan & Akurasi',
            self::PERSONALITY_PROFILE => 'Profil Kepribadian',
        };
    }
}

