<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    protected $fillable = [
        'land_owner_id',
        'title',
        'description',
        'address',
        'city',
        'price_per_month',
        'deposit',
        'terms_of_rental',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'room_length_m',
        'room_width_m',
        'room_height_m',
    ];

    protected function casts(): array
    {
        return [
            'price_per_month' => 'decimal:2',
            'deposit' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    public function landOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'land_owner_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function media(): HasMany
    {
        return $this->hasMany(PropertyMedia::class)->orderBy('sort_order');
    }

    public function rentalAgreements(): HasMany
    {
        return $this->hasMany(RentalAgreement::class);
    }

    public function primaryImageUrl(): ?string
    {
        $image = $this->relationLoaded('media')
            ? $this->media->where('type', 'image')->where('is_primary', true)->first()
                ?? $this->media->where('type', 'image')->first()
            : null;

        if ($image) {
            return $image->url;
        }

        return $this->media()->where('type', 'image')->where('is_primary', true)->value('url')
            ?? $this->media()->where('type', 'image')->value('url');
    }

    public function arModelUrl(): ?string
    {
        $model = $this->relationLoaded('media')
            ? $this->media->where('type', 'ar_model')->first()
            : $this->media()->where('type', 'ar_model')->first();

        return $model?->url;
    }

    public function arModelIosUrl(): ?string
    {
        $model = $this->relationLoaded('media')
            ? $this->media->where('type', 'ar_model_ios')->first()
            : $this->media()->where('type', 'ar_model_ios')->first();

        return $model?->url;
    }
}
