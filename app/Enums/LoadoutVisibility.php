<?php

namespace App\Enums;

enum LoadoutVisibility: string
{
    case Private = 'private';
    case Unlisted = 'unlisted';
    case Public = 'public';
}
