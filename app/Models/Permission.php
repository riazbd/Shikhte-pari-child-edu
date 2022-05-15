<?php

namespace App\Models;

class Permission extends \Spatie\Permission\Models\Permission
{
    protected $guard_name = 'api';
}
