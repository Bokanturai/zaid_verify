<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\HasDateFilter;

class BonusHistory extends Model
{
    use HasDateFilter;
   protected $fillable = [
        'user_id',
        'referred_user_id',
        'amount',
        'type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }
}
