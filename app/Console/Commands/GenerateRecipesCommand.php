<?php

namespace App\Console\Commands;

use App\Models\Recipe;
use Faker\Factory as Faker;
use Illuminate\Console\Command;

class GenerateRecipesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recipes:generate 
                            {--count=10 : Number of recipes to generate}
                            {--min-ingredients=3 : Minimum number of ingredients per recipe}
                            {--max-ingredients=12 : Maximum number of ingredients per recipe}
                            {--min-steps=3 : Minimum number of steps per recipe}
                            {--max-steps=10 : Maximum number of steps per recipe}
                            {--min-authors=1 : Minimum number of authors per recipe}
                            {--max-authors=2 : Maximum number of authors per recipe}
                            {--image-source=picsum : Image source (picsum, loremflickr)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate fake recipe data with authors, ingredients, and steps using Faker';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $faker = Faker::create();

        $count = (int) $this->option('count');
        $minIngredients = (int) $this->option('min-ingredients');
        $maxIngredients = (int) $this->option('max-ingredients');
        $minSteps = (int) $this->option('min-steps');
        $maxSteps = (int) $this->option('max-steps');
        $minAuthors = (int) $this->option('min-authors');
        $maxAuthors = (int) $this->option('max-authors');
        $imageSource = $this->option('image-source');

        $this->info("Generating {$count} recipes...");

        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();

        for ($i = 0; $i < $count; $i++) {
            $this->generateRecipe($faker, $minIngredients, $maxIngredients, $minSteps, $maxSteps, $minAuthors, $maxAuthors, $imageSource);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("Successfully generated {$count} recipes!");
    }

    private function generateRecipe($faker, $minIngredients, $maxIngredients, $minSteps, $maxSteps, $minAuthors, $maxAuthors, $imageSource)
    {
        // Generate recipe data
        $recipeName = $this->generateRecipeName($faker);

        $recipeData = [
            'name' => $recipeName,
            'description' => $faker->text(200),
            'prep_time' => $faker->numberBetween(5, 60),
            'cook_time' => $faker->numberBetween(10, 180),
            'servings' => $faker->numberBetween(1, 12),
            'image_url' => $this->generateImageUrl($imageSource),
        ];

        // Create or update recipe
        $recipe = Recipe::updateOrCreate(
            ['name' => $recipeName],
            $recipeData
        );

        // Generate authors
        $numAuthors = $faker->numberBetween($minAuthors, $maxAuthors);
        $recipe->authors()->delete();
        for ($j = 0; $j < $numAuthors; $j++) {
            $recipe->authors()->create([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'about' => $faker->optional(0.7)->text(100),
            ]);
        }

        // Generate ingredients
        $numIngredients = $faker->numberBetween($minIngredients, $maxIngredients);
        $recipe->ingredients()->delete();
        $usedIngredients = [];

        for ($j = 0; $j < $numIngredients; $j++) {
            $ingredient = $this->generateUniqueIngredient($faker, $usedIngredients);
            $usedIngredients[] = $ingredient['name'];

            $recipe->ingredients()->create($ingredient);
        }

        // Generate steps
        $numSteps = $faker->numberBetween($minSteps, $maxSteps);
        $recipe->steps()->delete();

        for ($j = 0; $j < $numSteps; $j++) {
            $recipe->steps()->create([
                'title' => $this->generateStepTitle($faker, $j + 1),
                'description' => $faker->sentence(10),
                'order' => $j + 1,
            ]);
        }
    }

    private function generateRecipeName($faker)
    {
        $adjectives = ['Classic', 'Homemade', 'Delicious', 'Easy', 'Quick', 'Perfect', 'Traditional', 'Crispy', 'Creamy', 'Fresh', 'Spicy', 'Sweet', 'Savory'];
        $foods = [
            'Chocolate Chip Cookies', 'Pizza Margherita', 'Beef Stew', 'Chicken Soup', 'Apple Pie', 'Banana Bread',
            'Caesar Salad', 'Grilled Salmon', 'Pasta Carbonara', 'Vegetable Curry', 'Pancakes', 'Meatballs',
            'Fish Tacos', 'Mushroom Risotto', 'BBQ Ribs', 'Garlic Bread', 'Cheesecake', 'Stir Fry',
            'Lasagna', 'French Toast', 'Chicken Wings', 'Pad Thai', 'Burrito Bowl', 'Clam Chowder',
        ];

        return $faker->randomElement($adjectives).' '.$faker->randomElement($foods);
    }

    private function generateImageUrl($source)
    {
        $width = 640;
        $height = 480;

        switch ($source) {
            case 'loremflickr':
                return "https://loremflickr.com/{$width}/{$height}/food,recipe,cooking";
            case 'picsum':
            default:
                return "https://picsum.photos/{$width}/{$height}?random=".rand(1, 1000);
        }
    }

    private function generateUniqueIngredient($faker, $usedIngredients)
    {
        $commonIngredients = [
            'flour', 'sugar', 'salt', 'pepper', 'olive oil', 'butter', 'eggs', 'milk', 'cream', 'cheese',
            'onion', 'garlic', 'tomato', 'carrot', 'potato', 'bell pepper', 'mushroom', 'spinach',
            'chicken breast', 'ground beef', 'salmon', 'bacon', 'rice', 'pasta', 'bread', 'lemon',
            'herbs', 'spices', 'vanilla extract', 'baking powder', 'honey', 'soy sauce', 'vinegar',
        ];

        $units = ['cups', 'tbsp', 'tsp', 'lbs', 'oz', 'cloves', 'slices', null];

        do {
            $ingredient = $faker->randomElement($commonIngredients);
        } while (in_array($ingredient, $usedIngredients));

        return [
            'name' => $ingredient,
            'quantity' => $faker->randomFloat(2, 0.25, 4),
            'unit' => $faker->randomElement($units),
        ];
    }

    private function generateStepTitle($faker, $stepNumber)
    {
        $stepTitles = [
            'Prepare ingredients', 'Preheat oven', 'Mix dry ingredients', 'Combine wet ingredients',
            'Heat oil', 'Sauté vegetables', 'Add seasoning', 'Simmer', 'Bake', 'Cool', 'Serve',
            'Chop vegetables', 'Marinate', 'Grill', 'Boil water', 'Drain', 'Garnish', 'Chill',
        ];

        if ($stepNumber === 1) {
            return $faker->randomElement(['Prepare ingredients', 'Preheat oven', 'Heat oil']);
        }

        return $faker->randomElement($stepTitles);
    }
}
