<?php

namespace App\Enums;

enum QuestionTypeEnum: string
{
    case MULTIPLE_CHOICE_SINGLE = 'multiple_choice_single';
    case MULTIPLE_CHOICE_IMAGE = 'multiple_choice_image';
    case TRUE_FALSE = 'true_false';
    case FILL_BLANK = 'fill_blank';
    case SEQUENCE = 'sequence';
    case MATCHING = 'matching';
    case PERSONALITY_FORCED_CHOICE = 'personality_forced_choice';
    case PERSONALITY_LIKERT_5 = 'personality_likert_5';
    case UPLOAD_IMAGE = 'upload_image';
    case PAULI_GRID = 'pauli_grid';
    case MISSING_NUMBER = 'missing_number';
    case MISSING_LETTER = 'missing_letter';

    public function label(): string
    {
        return match ($this) {
            self::MULTIPLE_CHOICE_SINGLE => 'Pilihan Ganda',
            self::MULTIPLE_CHOICE_IMAGE => 'Pilihan Ganda Gambar',
            self::TRUE_FALSE => 'Benar / Salah',
            self::FILL_BLANK => 'Isian Kosong',
            self::SEQUENCE => 'Urutan',
            self::MATCHING => 'Mencocokkan',
            self::PERSONALITY_FORCED_CHOICE => 'Pilihan Paksa',
            self::PERSONALITY_LIKERT_5 => 'Likert 5',
            self::UPLOAD_IMAGE => 'Unggah Gambar',
            self::PAULI_GRID => 'Grid Pauli',
            self::MISSING_NUMBER => 'Angka Hilang',
            self::MISSING_LETTER => 'Huruf Hilang',
        };
    }

    public function supportsMiniQuiz(): bool
    {
        return $this->supportsPractice();
    }

    public function supportsPractice(): bool
    {
        return in_array($this, [
            self::MULTIPLE_CHOICE_SINGLE,
            self::MULTIPLE_CHOICE_IMAGE,
            self::TRUE_FALSE,
        ], true);
    }
}

