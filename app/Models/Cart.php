<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    protected $table = 'cart';

    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
    ];

    public function user(){
        return $this->hasMany(User::class,'id','user_id');
    }

    public function product() {
        return $this->hasMany(Product::class,'id','product_id');
    }
}
