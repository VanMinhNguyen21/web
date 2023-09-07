<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Category extends Model
{
    use HasFactory,HasApiTokens;

    protected $table = "category";

    protected $fillable = [
        'name',
        'description',
    ];

    public function product() {
        return $this->hasMany(Product::class,'category_id', 'id');
    }
}
