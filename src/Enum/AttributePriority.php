<?php

namespace App\Enum;

enum AttributePriority: string
{
    case Important = 'important';
    case Secondary = 'secondary';
    case Optional  = 'optional';
    
    
    public function getWeight(): int {
        return match($this) {
            self::Important => 10,
            self::Secondary => 5,
            self::Optional => 1,
        };
    }
}