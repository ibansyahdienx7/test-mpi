<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $table = 'reviews';
    protected $guared = ['id'];

    protected $fillable = [
        'user_id',
        'id_product',
        'rate',
        'name',
        'review',
        'created_at',
        'updated_at',
    ];
}
