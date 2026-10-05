<?php

namespace App\Enums;

enum Role: string
{
    case Administrator = 'administrator';
    case Viewer = 'viewer';

    public function label(): string
    {
        return match ($this) {
            self::Administrator => 'Administrator',
            self::Viewer => 'Viewer',
        };
    }
}