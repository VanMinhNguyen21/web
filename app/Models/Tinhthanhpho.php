<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tinhthanhpho extends Model
{
    use HasFactory;
    protected $table = 'tinh_thanhpho';

    protected $fillable = [
        'matp',
        'name',
        'type',
        'slug'
    ];
}
