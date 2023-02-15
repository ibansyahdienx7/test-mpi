<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaUser extends Model
{
    use HasFactory;

    protected $table = 'va_users';
    protected $guarded = ['id'];
}
