<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuanHuyen extends Model
{
    use HasFactory;

    protected $table = 'quan_huyen';
    protected $fillable = [
        'maqh',
        'name',
        'type',
        'matp',
    ];
}
