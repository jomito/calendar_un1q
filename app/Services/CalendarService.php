<?php

namespace App\Services;

use App\Domain\Entities\Event;
use App\Domain\Repositories\EventRepository;
use Illuminate\Support\Facades\Validator;

class CalendarService
{
    protected $eventRepository;

    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function addEvent($data)
    {
        $event = new Event($data);
        $event->validate();
        $this->eventRepository->add($event);
        return $event->toArray();
    }

    public function updateEvent($eventId, $data)
    {
        $event = $this->eventRepository->get($eventId);
        if ($event) {
            $event->fill($data);
            $event->validate();
            $this->eventRepository->update($event);
            return $event->toArray();
        } else {
            throw new \Exception('Event not found');
        }
    }

    public function listEvents($data)
    {
        $rules = [
            'start' => 'required|date|date_format:Y-m-d\TH:i',
            'end' => 'required|date|date_format:Y-m-d\TH:i',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            $errors = $validator->errors();
            throw new \InvalidArgumentException(implode(' ', $errors->all()));
        }

        $start = $data['start'];
        $end = $data['end'];
        $page = $data['page'] ?? 1;
        $perPage = $data['per_page'] ?? 10;

        $events = $this->eventRepository->list($start, $end, $page, $perPage);
        return $events->toArray();
    }

    public function getSchedule($data)
    {
        $rules = [
            'start' => 'required|date|date_format:Y-m-d\TH:i',
            'end' => 'required|date|date_format:Y-m-d\TH:i',
            'eventIds' => 'nullable|string',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            $errors = $validator->errors();
            throw new \InvalidArgumentException(implode(' ', $errors->all()));
        }

        $start = $data['start'];
        $end = $data['end'];
        $eventIds = isset($data['eventIds']) ? explode(',', $data['eventIds']) : [];
        $page = $data['page'] ?? 1;
        $perPage = $data['per_page'] ?? 10;

        return $this->eventRepository->getSchedule($start, $end, $eventIds, $page, $perPage);
    }

    public function deleteEvent($eventId)
    {
        $this->eventRepository->delete($eventId);
    }
}
