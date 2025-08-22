<?php

namespace Database\Seeders;

use App\Models\Recipe;
use Illuminate\Database\Seeder;

class RecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $recipes = [
            [
                'name' => 'Classic Chocolate Chip Cookies',
                'description' => 'These are the perfect chewy chocolate chip cookies with crispy edges and soft centers. A family favorite that never disappoints!',
                'prep_time' => 15,
                'cook_time' => 10,
                'servings' => 24,
                'authors' => [
                    ['name' => 'Sarah Baker', 'email' => 'baker@sweetsmiles.com', 'about' => 'Professional baker with 10 years experience'],
                ],
                'ingredients' => [
                    ['name' => 'all-purpose flour', 'quantity' => 2.25, 'unit' => 'cups'],
                    ['name' => 'baking soda', 'quantity' => 1, 'unit' => 'tsp'],
                    ['name' => 'salt', 'quantity' => 1, 'unit' => 'tsp'],
                    ['name' => 'butter, softened', 'quantity' => 1, 'unit' => 'cup'],
                    ['name' => 'granulated sugar', 'quantity' => 0.75, 'unit' => 'cup'],
                    ['name' => 'brown sugar', 'quantity' => 0.75, 'unit' => 'cup'],
                    ['name' => 'eggs', 'quantity' => 2, 'unit' => null],
                    ['name' => 'vanilla extract', 'quantity' => 2, 'unit' => 'tsp'],
                    ['name' => 'chocolate chips', 'quantity' => 2, 'unit' => 'cups'],
                ],
                'steps' => [
                    ['title' => 'Preheat', 'description' => 'Preheat oven to 375°F (190°C)'],
                    ['title' => 'Mix dry ingredients', 'description' => 'Mix flour, baking soda, and salt in a bowl'],
                    ['title' => 'Cream butter', 'description' => 'Cream butter and both sugars until fluffy'],
                    ['title' => 'Add eggs', 'description' => 'Beat in eggs and vanilla'],
                    ['title' => 'Combine', 'description' => 'Gradually add flour mixture'],
                    ['title' => 'Add chips', 'description' => 'Stir in chocolate chips'],
                    ['title' => 'Shape', 'description' => 'Drop rounded tablespoons on ungreased cookie sheets'],
                    ['title' => 'Bake', 'description' => 'Bake 9-11 minutes until golden brown'],
                    ['title' => 'Cool', 'description' => 'Cool on baking sheet for 2 minutes before removing'],
                ],
            ],
            [
                'name' => 'Homemade Pizza Dough',
                'description' => 'Perfect pizza dough recipe that yields a crispy yet chewy crust. Great for making authentic pizzas at home.',
                'prep_time' => 20,
                'cook_time' => 15,
                'servings' => 4,
                'authors' => [
                    ['name' => 'Mario Rossi', 'email' => 'chef@pizzapalace.com', 'about' => 'Italian chef specializing in traditional pizzas'],
                ],
                'ingredients' => [
                    ['name' => 'bread flour', 'quantity' => 3.5, 'unit' => 'cups'],
                    ['name' => 'sugar', 'quantity' => 1, 'unit' => 'tsp'],
                    ['name' => 'instant dry yeast', 'quantity' => 1, 'unit' => 'envelope'],
                    ['name' => 'kosher salt', 'quantity' => 2, 'unit' => 'tsp'],
                    ['name' => 'warm water', 'quantity' => 1.25, 'unit' => 'cups'],
                    ['name' => 'olive oil', 'quantity' => 2, 'unit' => 'tbsp'],
                ],
                'steps' => [
                    ['title' => 'Mix ingredients', 'description' => 'Combine flour, sugar, yeast, and salt in a large bowl'],
                    ['title' => 'Add liquids', 'description' => 'Make a well in center and add water and oil'],
                    ['title' => 'Form dough', 'description' => 'Mix until a sticky dough forms'],
                    ['title' => 'Knead', 'description' => 'Knead on floured surface for 10 minutes'],
                    ['title' => 'First rise', 'description' => 'Place in oiled bowl, cover, and rise for 1 hour'],
                    ['title' => 'Divide', 'description' => 'Punch down and divide into 2 portions'],
                    ['title' => 'Shape', 'description' => 'Roll out to desired thickness'],
                    ['title' => 'Bake', 'description' => 'Add toppings and bake at 475°F for 12-15 minutes'],
                ],
            ],
            [
                'name' => 'Beef and Vegetable Stew',
                'description' => 'A hearty, comforting stew perfect for cold days. Tender beef with carrots, potatoes, and herbs in a rich broth.',
                'prep_time' => 30,
                'cook_time' => 120,
                'servings' => 6,
                'authors' => [
                    ['name' => 'James Thompson', 'email' => 'chef@heartyhome.com', 'about' => 'Comfort food specialist'],
                ],
                'ingredients' => [
                    ['name' => 'beef chuck, cubed', 'quantity' => 2, 'unit' => 'lbs'],
                    ['name' => 'vegetable oil', 'quantity' => 3, 'unit' => 'tbsp'],
                    ['name' => 'onion, diced', 'quantity' => 1, 'unit' => null],
                    ['name' => 'garlic, minced', 'quantity' => 3, 'unit' => 'cloves'],
                    ['name' => 'tomato paste', 'quantity' => 3, 'unit' => 'tbsp'],
                    ['name' => 'beef broth', 'quantity' => 4, 'unit' => 'cups'],
                    ['name' => 'red wine', 'quantity' => 1, 'unit' => 'cup'],
                    ['name' => 'carrots, sliced', 'quantity' => 3, 'unit' => null],
                    ['name' => 'potatoes, cubed', 'quantity' => 4, 'unit' => null],
                    ['name' => 'bay leaves', 'quantity' => 2, 'unit' => null],
                    ['name' => 'fresh thyme', 'quantity' => 1, 'unit' => 'tbsp'],
                    ['name' => 'salt and pepper', 'quantity' => 1, 'unit' => 'to taste'],
                ],
                'steps' => [
                    ['title' => 'Season beef', 'description' => 'Season beef with salt and pepper'],
                    ['title' => 'Brown beef', 'description' => 'Heat oil in Dutch oven and brown beef on all sides'],
                    ['title' => 'Set aside', 'description' => 'Remove beef and set aside'],
                    ['title' => 'Sauté onions', 'description' => 'Sauté onion until translucent'],
                    ['title' => 'Add aromatics', 'description' => 'Add garlic and tomato paste, cook 1 minute'],
                    ['title' => 'Deglaze', 'description' => 'Add wine and scrape up browned bits'],
                    ['title' => 'Combine', 'description' => 'Return beef, add broth, bay leaves, and thyme'],
                    ['title' => 'First simmer', 'description' => 'Bring to boil, then simmer covered for 1 hour'],
                    ['title' => 'Add vegetables', 'description' => 'Add carrots and potatoes, simmer 45 minutes'],
                    ['title' => 'Finish', 'description' => 'Remove bay leaves and serve hot'],
                ],
            ],
            [
                'name' => 'Vanilla Bean Cheesecake',
                'description' => 'Rich and creamy New York style cheesecake with real vanilla bean and a graham cracker crust.',
                'prep_time' => 30,
                'cook_time' => 60,
                'servings' => 12,
                'authors' => [
                    ['name' => 'Emily Chen', 'email' => 'pastry@sweetdreams.com', 'about' => 'Pastry chef specializing in desserts'],
                ],
                'ingredients' => [
                    ['name' => 'graham cracker crumbs', 'quantity' => 2, 'unit' => 'cups'],
                    ['name' => 'melted butter', 'quantity' => 0.5, 'unit' => 'cup'],
                    ['name' => 'cream cheese, softened', 'quantity' => 2, 'unit' => 'lbs'],
                    ['name' => 'sugar', 'quantity' => 1, 'unit' => 'cup'],
                    ['name' => 'eggs', 'quantity' => 4, 'unit' => null],
                    ['name' => 'vanilla bean', 'quantity' => 1, 'unit' => null],
                    ['name' => 'vanilla extract', 'quantity' => 1, 'unit' => 'tsp'],
                    ['name' => 'sour cream', 'quantity' => 0.5, 'unit' => 'cup'],
                    ['name' => 'heavy cream', 'quantity' => 0.25, 'unit' => 'cup'],
                ],
                'steps' => [
                    ['title' => 'Preheat', 'description' => 'Preheat oven to 325°F'],
                    ['title' => 'Make crust', 'description' => 'Mix graham cracker crumbs with melted butter'],
                    ['title' => 'Press crust', 'description' => 'Press into bottom of 9-inch springform pan'],
                    ['title' => 'Beat cheese', 'description' => 'Beat cream cheese until smooth'],
                    ['title' => 'Add sugar', 'description' => 'Gradually add sugar, beating until fluffy'],
                    ['title' => 'Add eggs', 'description' => 'Add eggs one at a time'],
                    ['title' => 'Add vanilla', 'description' => 'Scrape vanilla bean seeds and add with extract'],
                    ['title' => 'Add cream', 'description' => 'Mix in sour cream and heavy cream'],
                    ['title' => 'Bake', 'description' => 'Pour over crust and bake 50-60 minutes'],
                    ['title' => 'Cool', 'description' => 'Cool completely, then refrigerate 4 hours'],
                ],
            ],
            [
                'name' => 'Thai Green Curry',
                'description' => 'Authentic Thai green curry with coconut milk, vegetables, and aromatic spices. Customize with your choice of protein.',
                'prep_time' => 20,
                'cook_time' => 30,
                'servings' => 4,
                'authors' => [
                    ['name' => 'Somchai Prasert', 'email' => 'chef@thaispice.com', 'about' => 'Thai cuisine expert'],
                ],
                'ingredients' => [
                    ['name' => 'green curry paste', 'quantity' => 2, 'unit' => 'tbsp'],
                    ['name' => 'coconut milk', 'quantity' => 1, 'unit' => 'can'],
                    ['name' => 'chicken thighs, sliced', 'quantity' => 1, 'unit' => 'lb'],
                    ['name' => 'fish sauce', 'quantity' => 2, 'unit' => 'tbsp'],
                    ['name' => 'brown sugar', 'quantity' => 1, 'unit' => 'tbsp'],
                    ['name' => 'eggplant, cubed', 'quantity' => 1, 'unit' => null],
                    ['name' => 'bell pepper, sliced', 'quantity' => 1, 'unit' => null],
                    ['name' => 'bamboo shoots', 'quantity' => 0.5, 'unit' => 'cup'],
                    ['name' => 'Thai basil leaves', 'quantity' => 1, 'unit' => 'cup'],
                    ['name' => 'kaffir lime leaves', 'quantity' => 2, 'unit' => null],
                    ['name' => 'red chili, sliced', 'quantity' => 1, 'unit' => null],
                    ['name' => 'jasmine rice', 'quantity' => 2, 'unit' => 'cups'],
                ],
                'steps' => [
                    ['title' => 'Heat coconut milk', 'description' => 'Heat thick coconut milk in wok over medium heat'],
                    ['title' => 'Fry paste', 'description' => 'Add curry paste and fry until fragrant'],
                    ['title' => 'Cook chicken', 'description' => 'Add chicken and cook until no longer pink'],
                    ['title' => 'Add liquids', 'description' => 'Add remaining coconut milk, fish sauce, and sugar'],
                    ['title' => 'Add eggplant', 'description' => 'Bring to simmer and add eggplant'],
                    ['title' => 'Cook vegetables', 'description' => 'Cook 10 minutes, then add bell pepper and bamboo shoots'],
                    ['title' => 'Simmer', 'description' => 'Simmer until vegetables are tender'],
                    ['title' => 'Add herbs', 'description' => 'Stir in basil, lime leaves, and chili'],
                    ['title' => 'Serve', 'description' => 'Serve over jasmine rice'],
                ],
            ],
            [
                'name' => 'Chocolate Lava Cake',
                'description' => 'Individual chocolate cakes with molten chocolate centers. Perfect dessert for chocolate lovers!',
                'prep_time' => 10,
                'cook_time' => 14,
                'servings' => 4,
                'authors' => [
                    ['name' => 'Pierre Dubois', 'email' => 'pastry@chocohaven.com', 'about' => 'French pastry chef'],
                ],
                'ingredients' => [
                    ['name' => 'dark chocolate, chopped', 'quantity' => 4, 'unit' => 'oz'],
                    ['name' => 'butter', 'quantity' => 4, 'unit' => 'tbsp'],
                    ['name' => 'eggs', 'quantity' => 2, 'unit' => null],
                    ['name' => 'granulated sugar', 'quantity' => 2, 'unit' => 'tbsp'],
                    ['name' => 'all-purpose flour', 'quantity' => 2, 'unit' => 'tbsp'],
                    ['name' => 'salt', 'quantity' => 1, 'unit' => 'pinch'],
                    ['name' => 'butter for ramekins', 'quantity' => 1, 'unit' => 'tbsp'],
                    ['name' => 'cocoa powder', 'quantity' => 2, 'unit' => 'tbsp'],
                    ['name' => 'vanilla ice cream', 'quantity' => 4, 'unit' => 'scoops'],
                ],
                'steps' => [
                    ['title' => 'Preheat', 'description' => 'Preheat oven to 425°F'],
                    ['title' => 'Prepare ramekins', 'description' => 'Butter four 6-oz ramekins and dust with cocoa'],
                    ['title' => 'Melt chocolate', 'description' => 'Melt chocolate and butter in microwave'],
                    ['title' => 'Whisk eggs', 'description' => 'Whisk eggs and sugar until thick'],
                    ['title' => 'Combine', 'description' => 'Stir in melted chocolate mixture'],
                    ['title' => 'Add flour', 'description' => 'Fold in flour and salt'],
                    ['title' => 'Fill ramekins', 'description' => 'Divide batter among ramekins'],
                    ['title' => 'Bake', 'description' => 'Bake 12-14 minutes until edges are firm'],
                    ['title' => 'Unmold', 'description' => 'Let stand 1 minute, then invert onto plates'],
                    ['title' => 'Serve', 'description' => 'Serve immediately with ice cream'],
                ],
            ],
            [
                'name' => 'Mediterranean Quinoa Salad',
                'description' => 'Fresh and healthy quinoa salad with Mediterranean flavors. Perfect for meal prep or light lunches.',
                'prep_time' => 15,
                'cook_time' => 15,
                'servings' => 6,
                'authors' => [
                    ['name' => 'Maria Papadopoulos', 'email' => 'healthy@medkitchen.com', 'about' => 'Mediterranean cuisine specialist'],
                ],
                'ingredients' => [
                    ['name' => 'quinoa', 'quantity' => 1, 'unit' => 'cup'],
                    ['name' => 'vegetable broth', 'quantity' => 2, 'unit' => 'cups'],
                    ['name' => 'cucumber, diced', 'quantity' => 1, 'unit' => null],
                    ['name' => 'tomatoes, diced', 'quantity' => 2, 'unit' => null],
                    ['name' => 'red onion, finely diced', 'quantity' => 0.5, 'unit' => null],
                    ['name' => 'kalamata olives', 'quantity' => 0.5, 'unit' => 'cup'],
                    ['name' => 'feta cheese, crumbled', 'quantity' => 0.5, 'unit' => 'cup'],
                    ['name' => 'fresh parsley', 'quantity' => 0.25, 'unit' => 'cup'],
                    ['name' => 'fresh mint', 'quantity' => 2, 'unit' => 'tbsp'],
                    ['name' => 'olive oil', 'quantity' => 3, 'unit' => 'tbsp'],
                    ['name' => 'lemon juice', 'quantity' => 2, 'unit' => 'tbsp'],
                    ['name' => 'oregano', 'quantity' => 1, 'unit' => 'tsp'],
                    ['name' => 'salt and pepper', 'quantity' => 1, 'unit' => 'to taste'],
                ],
                'steps' => [
                    ['title' => 'Rinse quinoa', 'description' => 'Rinse quinoa under cold water'],
                    ['title' => 'Cook quinoa', 'description' => 'Cook quinoa in vegetable broth until tender'],
                    ['title' => 'Cool', 'description' => 'Let quinoa cool completely'],
                    ['title' => 'Prep vegetables', 'description' => 'Dice cucumber, tomatoes, and red onion'],
                    ['title' => 'Chop herbs', 'description' => 'Chop fresh herbs'],
                    ['title' => 'Make dressing', 'description' => 'Whisk together oil, lemon juice, and oregano'],
                    ['title' => 'Combine', 'description' => 'Combine quinoa with vegetables and herbs'],
                    ['title' => 'Add cheese', 'description' => 'Add feta cheese and olives'],
                    ['title' => 'Dress', 'description' => 'Toss with dressing and season with salt and pepper'],
                    ['title' => 'Chill', 'description' => 'Chill for at least 30 minutes before serving'],
                ],
            ],
            [
                'name' => 'Classic French Onion Soup',
                'description' => 'Traditional French onion soup with caramelized onions in rich beef broth, topped with Gruyère cheese.',
                'prep_time' => 15,
                'cook_time' => 90,
                'servings' => 6,
                'authors' => [
                    ['name' => 'Jacques Martin', 'email' => 'chef@frenchbistro.com', 'about' => 'French cuisine chef'],
                ],
                'ingredients' => [
                    ['name' => 'yellow onions, sliced', 'quantity' => 6, 'unit' => null],
                    ['name' => 'butter', 'quantity' => 4, 'unit' => 'tbsp'],
                    ['name' => 'olive oil', 'quantity' => 2, 'unit' => 'tbsp'],
                    ['name' => 'sugar', 'quantity' => 1, 'unit' => 'tsp'],
                    ['name' => 'salt', 'quantity' => 1, 'unit' => 'tsp'],
                    ['name' => 'beef broth', 'quantity' => 4, 'unit' => 'cups'],
                    ['name' => 'chicken broth', 'quantity' => 2, 'unit' => 'cups'],
                    ['name' => 'dry white wine', 'quantity' => 0.5, 'unit' => 'cup'],
                    ['name' => 'bay leaves', 'quantity' => 2, 'unit' => null],
                    ['name' => 'fresh thyme', 'quantity' => 1, 'unit' => 'tbsp'],
                    ['name' => 'French bread', 'quantity' => 6, 'unit' => 'slices'],
                    ['name' => 'Gruyère cheese, grated', 'quantity' => 1.5, 'unit' => 'cups'],
                ],
                'steps' => [
                    ['title' => 'Heat fats', 'description' => 'Heat butter and oil in large pot'],
                    ['title' => 'Add onions', 'description' => 'Add onions, sugar, and salt'],
                    ['title' => 'Caramelize', 'description' => 'Cook onions slowly for 45 minutes until caramelized'],
                    ['title' => 'Add wine', 'description' => 'Add wine and cook 2 minutes'],
                    ['title' => 'Add broth', 'description' => 'Add broths, bay leaves, and thyme'],
                    ['title' => 'Simmer', 'description' => 'Simmer 30 minutes'],
                    ['title' => 'Toast bread', 'description' => 'Toast bread slices'],
                    ['title' => 'Portion', 'description' => 'Ladle soup into oven-safe bowls'],
                    ['title' => 'Top', 'description' => 'Top with bread and cheese'],
                    ['title' => 'Broil', 'description' => 'Broil until cheese is bubbly and golden'],
                ],
            ],
            [
                'name' => 'Banana Bread',
                'description' => 'Moist and delicious banana bread made with ripe bananas and a hint of cinnamon. Perfect for breakfast or snacking.',
                'prep_time' => 10,
                'cook_time' => 65,
                'servings' => 8,
                'authors' => [
                    ['name' => 'Betty Johnson', 'email' => 'baker@homecomfort.com', 'about' => 'Home baking enthusiast'],
                ],
                'ingredients' => [
                    ['name' => 'ripe bananas, mashed', 'quantity' => 3, 'unit' => null],
                    ['name' => 'melted butter', 'quantity' => 0.33, 'unit' => 'cup'],
                    ['name' => 'sugar', 'quantity' => 0.75, 'unit' => 'cup'],
                    ['name' => 'egg, beaten', 'quantity' => 1, 'unit' => null],
                    ['name' => 'vanilla extract', 'quantity' => 1, 'unit' => 'tsp'],
                    ['name' => 'baking soda', 'quantity' => 1, 'unit' => 'tsp'],
                    ['name' => 'salt', 'quantity' => 1, 'unit' => 'pinch'],
                    ['name' => 'all-purpose flour', 'quantity' => 1.5, 'unit' => 'cups'],
                    ['name' => 'cinnamon', 'quantity' => 0.5, 'unit' => 'tsp'],
                    ['name' => 'chopped walnuts', 'quantity' => 0.5, 'unit' => 'cup'],
                ],
                'steps' => [
                    ['title' => 'Preheat', 'description' => 'Preheat oven to 350°F'],
                    ['title' => 'Grease pan', 'description' => 'Grease a 4x8 inch loaf pan'],
                    ['title' => 'Mix wet', 'description' => 'Mix melted butter with mashed bananas'],
                    ['title' => 'Add sugar', 'description' => 'Stir in sugar, egg, and vanilla'],
                    ['title' => 'Add leavening', 'description' => 'Sprinkle baking soda and salt over mixture'],
                    ['title' => 'Add flour', 'description' => 'Add flour and cinnamon, mix until just combined'],
                    ['title' => 'Add nuts', 'description' => 'Fold in walnuts if using'],
                    ['title' => 'Pour', 'description' => 'Pour into prepared loaf pan'],
                    ['title' => 'Bake', 'description' => 'Bake 60-65 minutes until toothpick comes out clean'],
                    ['title' => 'Cool', 'description' => 'Cool in pan for 10 minutes before removing'],
                ],
            ],
            [
                'name' => 'Chicken Alfredo Pasta',
                'description' => 'Creamy chicken alfredo with perfectly cooked fettuccine in a rich parmesan sauce.',
                'prep_time' => 15,
                'cook_time' => 25,
                'servings' => 4,
                'authors' => [
                    ['name' => 'Antonio Verdi', 'email' => 'chef@italianclassic.com', 'about' => 'Italian pasta specialist'],
                ],
                'ingredients' => [
                    ['name' => 'fettuccine pasta', 'quantity' => 1, 'unit' => 'lb'],
                    ['name' => 'chicken breasts', 'quantity' => 2, 'unit' => null],
                    ['name' => 'butter', 'quantity' => 4, 'unit' => 'tbsp'],
                    ['name' => 'garlic, minced', 'quantity' => 3, 'unit' => 'cloves'],
                    ['name' => 'heavy cream', 'quantity' => 1, 'unit' => 'cup'],
                    ['name' => 'Parmesan cheese, grated', 'quantity' => 1, 'unit' => 'cup'],
                    ['name' => 'chicken broth', 'quantity' => 0.5, 'unit' => 'cup'],
                    ['name' => 'olive oil', 'quantity' => 2, 'unit' => 'tbsp'],
                    ['name' => 'salt and pepper', 'quantity' => 1, 'unit' => 'to taste'],
                    ['name' => 'fresh parsley', 'quantity' => 2, 'unit' => 'tbsp'],
                    ['name' => 'red pepper flakes', 'quantity' => 0.5, 'unit' => 'tsp'],
                ],
                'steps' => [
                    ['title' => 'Season chicken', 'description' => 'Season chicken with salt and pepper'],
                    ['title' => 'Heat oil', 'description' => 'Heat olive oil in large skillet'],
                    ['title' => 'Cook chicken', 'description' => 'Cook chicken until golden and cooked through'],
                    ['title' => 'Slice chicken', 'description' => 'Remove chicken and slice into strips'],
                    ['title' => 'Cook pasta', 'description' => 'Cook pasta according to package directions'],
                    ['title' => 'Sauté garlic', 'description' => 'In same skillet, melt butter and sauté garlic'],
                    ['title' => 'Add liquids', 'description' => 'Add cream and chicken broth, simmer'],
                    ['title' => 'Add cheese', 'description' => 'Stir in Parmesan cheese until melted'],
                    ['title' => 'Combine', 'description' => 'Add pasta and chicken to sauce'],
                    ['title' => 'Garnish', 'description' => 'Toss until well coated, garnish with parsley'],
                ],
            ],
        ];

        foreach ($recipes as $recipeData) {
            // Extract related data
            $authors = $recipeData['authors'];
            $ingredients = $recipeData['ingredients'];
            $steps = $recipeData['steps'];

            // Store recipe name for uniqueness check
            $recipeName = $recipeData['name'];

            // Remove related data from main recipe data
            unset($recipeData['authors'], $recipeData['ingredients'], $recipeData['steps']);

            // Use updateOrCreate with name for uniqueness
            // The Recipe model will auto-generate the slug
            $recipe = Recipe::updateOrCreate(
                ['name' => $recipeName],
                $recipeData
            );

            // Sync authors - delete existing and recreate
            $recipe->authors()->delete();
            foreach ($authors as $author) {
                $recipe->authors()->create($author);
            }

            // Sync ingredients - delete existing and recreate
            $recipe->ingredients()->delete();
            foreach ($ingredients as $ingredient) {
                $recipe->ingredients()->create($ingredient);
            }

            // Sync steps - delete existing and recreate
            $recipe->steps()->delete();
            foreach ($steps as $index => $step) {
                $step['order'] = $index + 1;
                $recipe->steps()->create($step);
            }
        }
    }
}
