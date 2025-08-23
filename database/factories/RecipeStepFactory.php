<?php

namespace Database\Factories;

use App\Models\Recipe;
use App\Models\RecipeStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RecipeStep>
 */
class RecipeStepFactory extends Factory
{
    protected $model = RecipeStep::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $stepTemplates = [
            'Preheat oven to desired temperature',
            'Mix dry ingredients in a large bowl',
            'Combine wet ingredients separately',
            'Gradually add wet ingredients to dry ingredients',
            'Pour mixture into prepared pan',
            'Bake until golden brown or toothpick comes out clean',
            'Let cool before serving',
            'Heat oil in a large pan over medium heat',
            'Add vegetables and cook until tender',
            'Season with salt and pepper to taste',
            'Simmer until flavors combine',
            'Serve hot and garnish as desired',
            'Stir continuously to prevent burning',
            'Cover and let rest for a few minutes',
            'Transfer to a serving dish',
        ];

        return [
            'recipe_id' => Recipe::factory(),
            'title' => $this->faker->optional(0.3)->sentence(3),
            'description' => $this->faker->randomElement($stepTemplates),
            'order' => $this->faker->numberBetween(1, 10),
            'completed' => false,
        ];
    }
}
