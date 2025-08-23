<?php

/**
 * Recipe configuration settings
 */
return [
    // Cache duration in seconds (default: 24 hours)
    'image_url_cache_time' => env('RECIPE_IMAGE_URL_CACHE_TIME', 86400),

    // Recipe categories
    'categories' => [
        'Appetizer', 'Breakfast', 'Lunch', 'Dinner', 'Dessert',
        'Snack', 'Beverage', 'Salad', 'Soup', 'Main Course',
        'Side Dish', 'Pasta', 'Seafood', 'Vegetarian', 'Vegan',
        'Gluten-Free', 'Keto', 'Mediterranean', 'Asian', 'Italian',
        'Mexican', 'American', 'French', 'Indian', 'Thai',
    ],
];
