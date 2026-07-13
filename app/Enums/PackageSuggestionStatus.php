<?php

namespace App\Enums;

enum PackageSuggestionStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Ignored = 'ignored';
    case Duplicate = 'duplicate';
    case FailedLookup = 'failed_lookup';
}
