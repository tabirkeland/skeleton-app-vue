<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeIngredient extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'recipe_id',
        'name',
        'quantity',
        'unit',
        'is_checked',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:2',
        'is_checked' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the recipe that owns the ingredient.
     */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * Get formatted ingredient string.
     */
    public function getFormattedAttribute(): string
    {
        $parts = [];
        if ($this->quantity != 1) {
            $parts[] = $this->quantity;
        }
        if ($this->unit) {
            $parts[] = $this->unit;
        }
        $parts[] = $this->name;

        return implode(' ', $parts);
    }
}
