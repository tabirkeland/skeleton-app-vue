<?php

namespace App\Models;

use App\Builders\RecipeBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Recipe extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'ingredients',
        'steps',
        'author_email',
        'slug',
    ];

    /**
     * Boot the model and register model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($recipe) {
            if (empty($recipe->slug)) {
                $recipe->slug = static::generateUniqueSlug($recipe->name);
            }
        });

        static::updating(function ($recipe) {
            if ($recipe->isDirty('name')) {
                $recipe->slug = static::generateUniqueSlug($recipe->name, $recipe->id);
            }
        });
    }

    /**
     * Generate a unique slug for the recipe.
     */
    private static function generateUniqueSlug(string $name, int $excludeId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (static::slugExists($slug, $excludeId)) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if a slug already exists.
     */
    private static function slugExists(string $slug, int $excludeId = null): bool
    {
        $query = static::where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'ingredients' => 'array',
        'steps' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \App\Builders\RecipeBuilder
     */
    public function newEloquentBuilder($query)
    {
        return new RecipeBuilder($query);
    }

    /**
     * Get the ingredient count accessor.
     */
    public function getIngredientCountAttribute(): int
    {
        return is_array($this->ingredients) ? count($this->ingredients) : 0;
    }

    /**
     * Get the step count accessor.
     */
    public function getStepCountAttribute(): int
    {
        return is_array($this->steps) ? count($this->steps) : 0;
    }
}
