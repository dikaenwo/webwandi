<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'table_number',
        'items',
        'subtotal',
        'tax',
        'total',
        'payment_method',
        'midtrans_order_id',
        'midtrans_token',
        'midtrans_redirect_url',
        'midtrans_transaction_id',
        'midtrans_payment_type',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'items'   => 'array',
        'paid_at' => 'datetime',
    ];
}
