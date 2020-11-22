<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'category',
        'target_funding',
        'target_end',
        'photo_path',
        'video_path',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['url_photo'];

    public function getUrlPhotoAttribute($value)
    {
        return url('') . Storage::url($this->photo_path);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function countTransactionsByProduct($productId)
    {
        return $this
            ->transactions()
            ->where('product_id', $productId)
            ->where('status', 'SUCCESS')
            ->get()
            ->pluck('total')
            ->sum();
    }

    public function getAllSuccessTransactionByProduct($productId)
    {
        return $this
            ->transactions()
            ->where('product_id', $productId)
            ->where('status', 'SUCCESS')
            ->with(['user' => function ($query) {
                $query->select('id', 'name', 'profile_photo_path');
            }])
            ->get(['message', 'total', 'created_at', 'user_id']);
    }

    public function user()
    {
        return $this->HasOne(User::class, 'id', 'user_id');
    }

    public function sortLatest()
    {
        return $this
            ->orderBy('created_at', 'desc');
    }
}
