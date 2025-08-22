<?php

namespace App\Models;

use App\Actions\Recipe\GetRecipeImageUrlAction;
use App\Builders\RecipeBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'slug',
        'category',
        'image_url',
        'prep_time',
        'cook_time',
        'servings',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'prep_time' => 'integer',
        'cook_time' => 'integer',
        'servings' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['authors', 'ingredients', 'steps'];

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
     * Get the authors for the recipe.
     */
    public function authors(): HasMany
    {
        return $this->hasMany(RecipeAuthor::class);
    }

    /**
     * Get the ingredients for the recipe.
     */
    public function ingredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    /**
     * Get the steps for the recipe.
     */
    public function steps(): HasMany
    {
        return $this->hasMany(RecipeStep::class)->orderBy('order');
    }

    /**
     * Get the primary author (first author).
     */
    public function getPrimaryAuthorAttribute()
    {
        return $this->authors->first();
    }

    /**
     * Get the primary author email.
     */
    public function getAuthorEmailAttribute()
    {
        return $this->primary_author?->email;
    }

    /**
     * Get ingredient count accessor.
     */
    public function getIngredientCountAttribute(): int
    {
        return $this->ingredients->count();
    }

    /**
     * Get step count accessor.
     */
    public function getStepCountAttribute(): int
    {
        return $this->steps->count();
    }

    /**
     * Get image URL for the recipe.
     * If no image_url is set, it will fetch one using the GetRecipeImageUrlAction.
     */
    public function getImageUrlAttribute($value)
    {
        // If we already have an image URL, return it
        if ($value) {
            return $value;
        }

        // Use the action to get an image URL
        $action = app(GetRecipeImageUrlAction::class);

        return $action->execute($this);
    }
}
