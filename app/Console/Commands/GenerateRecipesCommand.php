<?php

namespace App\Console\Commands;

use App\Actions\Recipe\GenerateRecipeAction;
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
    protected $description = 'Generate fake recipe data with authors, ingredients, and steps using Faker and FakerRestaurant';

    /**
     * Execute the console command.
     */
    public function handle(GenerateRecipeAction $generateRecipeAction)
    {
        $count = (int) $this->option('count');

        $this->info("Generating {$count} recipes with enhanced FakerRestaurant data...");

        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();

        // Prepare parameters for the action
        $parameters = [
            'count' => $count,
            'with_relationships' => true,
            'min_ingredients' => (int) $this->option('min-ingredients'),
            'max_ingredients' => (int) $this->option('max-ingredients'),
            'min_steps' => (int) $this->option('min-steps'),
            'max_steps' => (int) $this->option('max-steps'),
            'min_authors' => (int) $this->option('min-authors'),
            'max_authors' => (int) $this->option('max-authors'),
            'image_source' => $this->option('image-source'),
            'progress_callback' => function ($current, $total) use ($progressBar) {
                $progressBar->advance();
            },
        ];

        // Execute the action
        $recipes = $generateRecipeAction->execute($parameters);

        $progressBar->finish();
        $this->newLine();

        // Display summary
        $this->info("Successfully generated {$count} recipes!");

        if ($this->output->isVerbose()) {
            $this->newLine();
            $this->table(
                ['Recipe Name', 'Category', 'Ingredients', 'Steps', 'Authors'],
                collect($recipes)->map(fn ($recipe) => [
                    $recipe->name,
                    $recipe->category ?? 'N/A',
                    $recipe->ingredients->count(),
                    $recipe->steps->count(),
                    $recipe->authors->count(),
                ])->toArray()
            );
        } else {
            $this->info('Run with -v flag to see detailed recipe information.');
        }
    }
}
