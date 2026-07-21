<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Menu extends Model
{
    protected $fillable = [
        'name',
        'description',
        'category_id',
        'price',
        'has_hot',
        'price_hot',
        'desc_hot',
        'has_ice',
        'price_ice',
        'desc_ice',
        'image',
        'tag',
        'is_available',
        'sort_order',
        'rating',
        'reviews',
    ];

    /**
     * Append accessors to all serialization (JSON/array) so Alpine.js receives image_url.
     */
    protected $appends = ['image_url'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
            'has_hot'      => 'boolean',
            'has_ice'      => 'boolean',
            'price'        => 'integer',
            'price_hot'    => 'integer',
            'price_ice'    => 'integer',
            'rating'       => 'float',
            'reviews'      => 'integer',
        ];
    }

    /**
     * Get the full URL of the menu image or null if not set.
     */
    public function getImageUrlAttribute(): ?string
    {
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            return asset('storage/' . $this->image);
        }
        return null;
    }

    /**
     * Scope to only return available menu items.
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }
}
