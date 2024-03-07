<?php
// app/Http/Controllers/EventController.php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\RosterEvent;
use Illuminate\Http\Request;
use App\Services\RosterParserService;

class EventController extends Controller
{
    public function parseAndStoreRoster(Request $request, RosterParserService $rosterParser)
    {
        $request->validate([
            'file' => 'required|mimes:pdf,xls,xlsx,txt,html,ics|max:10240',
        ]);

        // $file = $request->file('file');
        if ($request->hasFile('file')) {
            $response = $rosterParser->parseAndStoreFile($request->file);
        }

        // You may want to return a success message or response code
        return response()->json(['message' => 'Roster parsed and stored successfully']);
    }

    public function getEventsBetweenDates(Request $request)
    {   
        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'));

        $events = RosterEvent::whereBetween('date', [$startDate, $endDate])->get();

        return response()->json($events);
    }

    public function getFlightsForNextWeek()
    {
        $startDate = Carbon::parse('14 Jan 2022')->format('Y-m-d');
        $endDate = Carbon::parse('14 Jan 2022')->addWeek()->format('Y-m-d');

        $flights = RosterEvent::whereBetween('date', [$startDate, $endDate])
            ->where('type', 'FLT')->get();

        return response()->json($flights);
    }

    public function getStandbyEventsForNextWeek()
    {
        $startDate = Carbon::parse('14 Jan 2022')->format('Y-m-d');
        $endDate = Carbon::parse('14 Jan 2022')->addWeek()->format('Y-m-d');

        $standbyEvents = RosterEvent::whereBetween('date', [$startDate, $endDate])
            ->where('type', 'SBY')->get();

        return response()->json($standbyEvents);
    }

    public function getFlightsByLocation(Request $request)
    {
        $location = $request->input('location');

        $flights = RosterEvent::where('start_location', $location)
            ->where('type', 'FLT')->get();

        return response()->json($flights);
    }
}

?>