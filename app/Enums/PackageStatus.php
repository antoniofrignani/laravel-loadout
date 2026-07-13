<?php

namespace App\Enums;

enum PackageStatus: string
{
    case Curated = 'curated';
    case Suggested = 'suggested';
    case Ignored = 'ignored';
}
