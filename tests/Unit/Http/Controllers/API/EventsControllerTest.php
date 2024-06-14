<?php

namespace Tests\Unit\Http\Controllers\API;

use Tests\Factories\EventFactory;
use Tests\TestCase;
use App\Domain\Repositories\EventRepository;
use App\Services\CalendarService;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;

class EventsControllerTest extends TestCase
{
    use WithFaker;

    protected $calendarService;
    protected $eventRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventRepository = new EventRepository();
        $this->calendarService = new CalendarService($this->eventRepository);

        DB::table('events')->truncate();
    }

    public function testAddEvent()
    {
        $data = [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'start' => now()->addHour()->format('Y-m-d\TH:i'),
            'end' => now()->addHours(2)->format('Y-m-d\TH:i'),
            'recurring_pattern' => [
                'frequency' => 'daily',
                'repeat_until' => now()->addWeek()->format('Y-m-d')
            ]
        ];

        $response = $this->postJson('/api/events', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => $data['title']]);

        $this->assertDatabaseHas('events', ['title' => $data['title']]);
    }

    public function testUpdateEvent()
    {
        $event = EventFactory::create();
        $this->eventRepository->add($event);

        $data = [
            'title' => 'Updated Title',
            'description' => $this->faker->paragraph,
            'start' => now()->addHour()->format('Y-m-d\TH:i'),
            'end' => now()->addHours(2)->format('Y-m-d\TH:i'),
            'recurring_pattern' => [
                'frequency' => 'daily',
                'repeat_until' => now()->addWeek()->format('Y-m-d')
            ]
        ];

        $response = $this->putJson("/api/events/{$event->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => $data['title']]);

        $this->assertDatabaseHas('events', ['title' => $data['title']]);
    }

    public function testListEvents()
    {
        $event1 = EventFactory::create(['end' => 1]);
        $event2 = EventFactory::create(['start' => 1, 'end' => 2]);
        $event3 = EventFactory::create(['start' => 2, 'end' => 3]);

        $this->eventRepository->add($event1);
        $this->eventRepository->add($event2);
        $this->eventRepository->add($event3);

        $start = now()->format('Y-m-d\TH:i');
        $end = now()->addWeek()->format('Y-m-d\TH:i');

        $response = $this->getJson("/api/events?start={$start}&end={$end}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'description', 'start', 'end', 'recurring_pattern']
                ]
            ]);
    }

    public function testGetSchedule()
    {
        $event1 = EventFactory::create(['end' => 1]);
        $event2 = EventFactory::create(['start' => 1, 'end' => 2]);

        $this->eventRepository->add($event1);
        $this->eventRepository->add($event2);

        $start = now()->format('Y-m-d\TH:i');
        $end = now()->addWeek()->format('Y-m-d\TH:i');

        $response = $this->getJson("/api/events/schedule?start={$start}&end={$end}");

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDeleteEvent()
    {
        $event = EventFactory::create();
        $this->eventRepository->add($event);

        $response = $this->deleteJson("/api/events/{$event->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }
}
