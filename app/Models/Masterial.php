<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Masterial extends Model
{
    use HasFactory;
    protected $table = 'masterial';
    
    protected $fillable = [
        'name',
        'description',
    ];

    public function product() {
        return $this->hasMany(Product::class,'category_id', 'id');
    }
}
