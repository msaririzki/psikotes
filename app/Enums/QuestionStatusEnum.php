<?php

namespace App\Enums;

enum QuestionStatusEnum: string
{
    case DRAFT = 'draft';
    case REVIEW = 'review';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draf',
            self::REVIEW => 'Tinjau',
            self::PUBLISHED => 'Terbit',
            self::ARCHIVED => 'Arsip',
        };
    }
}

