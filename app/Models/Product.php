<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'product';

    protected $fillable = [
        'name',
        'thumbnail',
        'category_id',
        'supplier_id',
        'price_old',
        'price_new',
        'quantity',
        'description',
        'status',
        'color',
        'material_id',
        'shape_id',

    ];

    public function category()
    {
        # code...
        return $this->hasOne(Category::class, 'id', 'category_id');
    }
    public function shape()
    {
        # code...
        return $this->hasOne(Shape::class, 'id', 'shape_id');
    }
    public function material()
    {
        # code...
        return $this->hasOne(Masterial::class, 'id', 'material_id');
    }
    public function imageProduct()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'id');
    }
    public function supplier()
    {
        # code...
        return $this->hasOne(Supplier::class, 'id', 'supplier_id');
    }
}
