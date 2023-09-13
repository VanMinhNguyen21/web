<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shape extends Model
{
    use HasFactory;

    protected $table = 'shape';
    
    protected $fillable = [
        'name',
        'description'
    ];

    public function product() {
        return $this->hasMany(Product::class,'category_id', 'id');
    }
}
