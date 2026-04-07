<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataVariation extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_name',
        'service_id',
        'name',
        'variation_amount',
        'variation_code',
        'convinience_fee',
        'status',
        'fixedPrice',
    ];

    protected $casts = [
        'fixedPrice' => 'string',
        'variation_amount' => 'string',
        'convinience_fee' => 'string',
    ];
}
