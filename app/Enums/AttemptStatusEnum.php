<?php

namespace App\Enums;

enum AttemptStatusEnum: string
{
    case DRAFT = 'draft';
    case IN_PROGRESS = 'in_progress';
    case SUBMITTED = 'submitted';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';
}
