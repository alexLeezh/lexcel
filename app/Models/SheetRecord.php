<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class SheetRecord extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, HasFactory;

    protected $table = 'sheet_record';
}
