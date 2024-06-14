<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Event;
use Exception;
use DateTime;
use DatePeriod;
use DateInterval;
use App\Domain\Enums\Frequency;

class EventRepository
{
    public function add(Event $event)
    {
        $this->validateEventOverlap($event);
        $event->save();
    }

    public function update(Event $event)
    {
        $this->validateEventOverlap($event, $event->id);
        $event->save();
    }

    public function get($eventId)
    {
        return Event::find($eventId);
    }

    public function list($start = null, $end = null, $page = 1, $perPage = 10)
    {
        $query = Event::query();

        if ($start) {
            $query->where('start', '>=', new \DateTime($start));
        }

        if ($end) {
            $query->where('end', '<=', new \DateTime($end));
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function delete($eventId)
    {
        Event::destroy($eventId);
    }

    protected function validateEventOverlap(Event $event, $excludeId = null)
    {
        $query = Event::where(function ($query) use ($event) {
            $query->orWhere(function ($query) use ($event) {
                $query->where('start', '<', $event->end)
                    ->where('end', '>', $event->start);
            });
        });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw new Exception("Event overlaps with an existing event");
        }
    }

    protected function getIntervalSpec($frequency)
    {
        switch ($frequency) {
            case 'daily':
                return 'P1D';
            case 'weekly':
                return 'P1W';
            case 'monthly':
                return 'P1M';
            case 'yearly':
                return 'P1Y';
            default:
                throw new \Exception('Invalid frequency');
        }
    }

    public function findByIds($ids = [])
    {
        if (empty($ids)) {
            return Event::all();
        }

        return Event::whereIn('id', $ids)->get();
    }

    public function getSchedule($startDate, $endDate, $eventIds = [], $page = 1, $perPage = 10)
    {
        $events = $this->findByIds($eventIds); //Made for simplicity.

        $occurrences = [];

        $start = new DateTime($startDate);
        $end = new DateTime($endDate);

        foreach ($events as $event) {
            $eventStart = new DateTime($event->start);
            $eventEnd = new DateTime($event->end);

            $repeatUntil = $end;
            if (!empty($event->recurring_pattern['repeat_until'])){
                $eventRepeatUntil = new DateTime($event->recurring_pattern['repeat_until']);
                if ($end > $eventRepeatUntil){
                    $repeatUntil = $eventRepeatUntil;
                }
            } else {
                $repeatUntil = $event->end;
            }

            $intervalSpec = $this->getIntervalSpec($event->recurring_pattern['frequency'] ?? Frequency::Daily->value);
            $period = new DatePeriod($eventStart, new DateInterval($intervalSpec), $repeatUntil);

            foreach ($period as $date) {
                $occurrenceStart = $date;
                $occurrenceEnd = clone $date;
                $occurrenceEnd->add($eventStart->diff($eventEnd));

                if ($occurrenceStart >= $start && $occurrenceEnd <= $end) {
                    $occurrences[] = [
                        'id' => $event->id,
                        'title' => $event->title,
                        'description' => $event->description,
                        'start' => $occurrenceStart->format('Y-m-d\TH:i'),
                        'end' => $occurrenceEnd->format('Y-m-d\TH:i')
                    ];
                }
            }
        }

        usort($occurrences, function ($a, $b) {
            return strtotime($a['start']) - strtotime($b['start']);
        });

        $startFrom = ($page - 1) * $perPage;
        $pagedOccurrences = array_slice($occurrences, $startFrom, $perPage);

        return [
            'current_page' => $page,
            'per_page' => $perPage,
            'data' => $pagedOccurrences,
            'total' => count($occurrences),
            'last_page' => round(count($occurrences) / $perPage),
        ];
    }
}
