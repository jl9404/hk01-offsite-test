<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
    ];
});

$factory->define(App\Models\Transaction::class, function (Faker\Generator $faker) {
	return [
		'transaction_id' => str_random(10),
		'reference_id' => str_random(10),
		'customer_name' => $faker->name,
		'customer_phone' => $faker->e164PhoneNumber,
		'currency' => $faker->randomElement(['HKD', 'CNY', 'JPY', 'USD', 'AUD', 'EUR']),
		'amount' => $faker->randomFloat(2, 1, 1000),
		'debug' => $faker->text,
		'paid_at' => $faker->dateTime('now', 'Asia/Hong_Kong'),
	];
});