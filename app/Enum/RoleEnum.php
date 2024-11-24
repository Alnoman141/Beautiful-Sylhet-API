<?php

namespace App\Enum;

enum RoleEnum : string
{
    case superadmin = 'superadmin';
    case admin = 'admin';
    case editor = 'editor';
}
