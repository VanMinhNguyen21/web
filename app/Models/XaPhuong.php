<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XaPhuong extends Model
{
    use HasFactory;
    protected $table = 'xa_phuong';
    
    protected $fillable = [
        'xaid',
        'name',
        'type',
        'maqh'
    ];
}
