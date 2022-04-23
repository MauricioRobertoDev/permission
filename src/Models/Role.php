<?php

namespace MrDev\Permission\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MrDev\Permission\Traits\HasPermissions;

class Role extends Model
{
    use HasFactory;
    use HasPermissions;

    protected $fillable = [
        'key',
        'name',
        'guard_name',
        'description',
    ];
}
