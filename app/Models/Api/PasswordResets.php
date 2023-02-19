<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordResets extends Model
{
    use HasFactory;
    protected $table = 'password_resets';
    protected $guarded = ['email'];

    protected $fillable = [
        'email',
        'token',
        'created_at',
        'updated_at'
    ];
}
