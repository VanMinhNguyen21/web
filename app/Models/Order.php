<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    const STATUS_CANCEL = 5;
    const STATUS_SUCCESS = 4;
    use HasFactory;

    protected $table = 'order';

    protected $fillable = [
        'order_code',
        'user_id',
        'total_price',
        'status',
        'address',
        'status_order',
        'staff_id',
        'name',
        'phone',
        'note',
        'payment_method'
    ];

    public function order_detail() {
        return $this->hasMany(OrderDetail::class,'order_id','id');
    }

    public function user() {
        return $this->hasOne(User::class,'id', 'user_id');
    }
}
