<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'product_id',
        'message',
        'total',
        'status',
        'payment_url'
    ];

    public function product()
    {
        return $this->HasOne(Product::class, 'id', 'product_id');
    }

    public function user()
    {
        return $this->HasOne(User::class, 'id', 'user_id');
    }
}

