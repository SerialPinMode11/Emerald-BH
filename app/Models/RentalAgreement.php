<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RentalAgreement extends Model
{
    protected $fillable = [
        'property_id',
        'customer_id',
        'community_id',
        'start_date',
        'end_date',
        'total_rent',
        'deposit_paid',
        'status',
        'community_notes',
        'super_admin_override',
        'signed_by_customer',
        'signed_by_owner',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'total_rent' => 'decimal:2',
            'deposit_paid' => 'boolean',
            'signed_by_customer' => 'datetime',
            'signed_by_owner' => 'datetime',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(User::class, 'community_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
