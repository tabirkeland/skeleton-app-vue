<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    // generate a URL of food with faker, ensure the image exists

    $faker = \Faker\Factory::create();

    $imageUrl = $faker->imageUrl(640, 480, 'food');
    // Ensure the image URL is valid and exists
    $imageHeaders = get_headers($imageUrl);
    if (!$imageHeaders || strpos($imageHeaders[0], '200') === false) {
        throw new \Exception("Image URL does not exist: $imageUrl");
    }


    dd($imageUrl);

    return view('welcome');
});
