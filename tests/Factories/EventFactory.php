<?php

namespace Tests\Factories;

use App\Domain\Entities\Event;
use Faker\Factory as Faker;
use DateTime;

class EventFactory
{
    public static function create($attributes = [])
    {
        $faker = Faker::create();
        $data = [
            'title' => $faker->sentence,
            'description' => $faker->paragraph,
            'start' => now()->addHour($attributes['start'] ?? 0)->format('Y-m-d\TH:i'),
            'end' => now()->addHours($attributes['end'] ?? 2)->format('Y-m-d\TH:i'),
            'recurring_pattern' => [
                'frequency' => 'daily',
                'repeat_until' => now()->addWeek()->format('Y-m-d')
            ]
        ];

        return new Event($data);
    }
}
