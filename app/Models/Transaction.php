<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\HasDateFilter;

class Transaction extends Model
{
    use HasFactory, HasDateFilter;

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'fee',
        'net_amount',
        'description',
        'status',
        'transaction_ref',
        'reference_id',
        'payer_name',
        'performed_by',
        'metadata',
        'service_type',
        'approved_by',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: A transaction is performed by a user (admin or staff)
     */
    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
