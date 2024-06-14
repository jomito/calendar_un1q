<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CalendarService;
use App\Http\Requests\EventRequest;

class EventsController extends Controller
{
    protected $calendarService;

    public function __construct(CalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    public function addEvent(EventRequest $request)
    {
        try {
            $event = $this->calendarService->addEvent($request->validated());
            return response()->json($event, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function updateEvent($eventId, EventRequest $request)
    {
        try {
            $event = $this->calendarService->updateEvent($eventId, $request->validated());
            return response()->json($event, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function listEvents(Request $request)
    {
        try {
            $events = $this->calendarService->listEvents($request->all());
            return response()->json($events, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function getSchedule(Request $request)
    {
        try {
            $schedule = $this->calendarService->getSchedule($request->all());
            return response()->json($schedule, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function deleteEvent($eventId)
    {
        try {
            $this->calendarService->deleteEvent($eventId);
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
