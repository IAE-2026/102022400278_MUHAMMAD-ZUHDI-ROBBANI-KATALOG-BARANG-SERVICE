<?php

namespace App\Enums;

enum ItemStatus: string
{
    case Draft = 'DRAFT';
    case Open = 'OPEN';
    case Closed = 'CLOSED';
    case Cancelled = 'CANCELLED';
}
