<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $countries = [
            [
                'name' => 'Уилмингтон (Северная Каролина)',
                'name_eng' => 'Wilmington (North carolina)'
            ],
            [
                'name' => 'Портленд (Орегон)',
                'name_eng' => 'Portland (OR)'
            ],
            [
                'name' => 'Торонто',
                'name_eng' => 'Toronto'
            ],
            [
                'name' => 'Варшава',
                'name_eng' => 'Warsaw'
            ],
            [
                'name' => 'Валенсия',
                'name_eng' => 'Valencia'
            ],
            [
                'name' => 'Шанхай',
                'name_eng' => 'Shanghai'
            ],
        ];

        if (count($countries) > Location::query()->count('id')) {
            $location = Location::query();
            User::factory(10)->create();
            foreach ($countries as $country) {
                $location->create([
                    'name' => $country['name'],
                    'name_eng' => $country['name_eng']
                ]);
            }

            User::all()->each(function (User $user) use ($location) {
                $user->locations()->attach($location->get()->random(),
                    ['reserved_blocs' => rand(1, 10),
                        'secret_key' => Str::random(12),
                        'created_at' => Carbon::now()->toDateTimeString(),
                        'money' => 180,
                        'deactivate' => Carbon::now()->addDays(10)->toDateTimeString()]);
            });
        }

    }
}
