<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use Carbon\Carbon;

class ApiBedController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(request('date_start')) {

            $requestedStart  = ((request('date_start')) ? Carbon::createFromFormat('d-m-Y', request('date_start'))->format('Y-m-d') : null);
            $requestedEnd  = ((request('date_end')) ? Carbon::createFromFormat('d-m-Y', request('date_end'))->format('Y-m-d') : null);

            $availableBeds = Bed::whereDoesntHave('bookings', function ($query) use ($requestedStart, $requestedEnd) {
                $query->where(function ($q) use ($requestedStart, $requestedEnd) {
                    // Check for any overlap with existing bookings
                    $q->where(function ($sub) use ($requestedStart, $requestedEnd) {
                        // Existing booking starts before or during requested period
                        $sub->where('date_start', '<=', $requestedEnd ?: '9999-12-31')
                            ->where(function ($inner) use ($requestedStart) {
                                // And it either has no end date (ongoing) or ends during/after requested start
                                $inner->whereNull('date_end')
                                      ->orWhere('date_end', '>=', $requestedStart);
                            });
                    });
                });
            })
            ->whereHas('room.house', function ($query) {
                $query->where('status', 'available');
            })
            ->get();
            
        } else {
            $availableBeds = Bed::all();
        }        

        $select2_array = array();
        $i = 0;

        foreach($availableBeds as $bed){

            $search = request('q');
            $text = ($bed->room->house->name ?? 'House').' | '.($bed->room->name ?? 'Room').' | '.$bed->name;

            if(strlen($search) > 0){
                if(str_contains(strtolower($text), $search)){
                    $select2_array[$i]['id'] = $bed->id;
                    $select2_array[$i]['text'] = $text;
                        
                    $i++;
                }
            } else {
                $select2_array[$i]['id'] = $bed->id;
                $select2_array[$i]['text'] = $text;
                    
                $i++;
            }
        }

        return response()->json($select2_array);
    }

    /**
     * Get unavailable date ranges for a specific bed
     *
     * @return \Illuminate\Http\Response
     */
    public function getUnavailableDates($bedId)
    {
        $bed = Bed::find($bedId);
        
        if (!$bed) {
            return response()->json(['error' => 'Bed not found'], 404);
        }

        // Get all bookings for this bed, ordered by date
        $bookings = $bed->bookings()
            ->orderBy('date_start')
            ->get(['date_start', 'date_end']);

        $unavailableDates = [];

        foreach ($bookings as $booking) {
            $start = Carbon::parse($booking->date_start);
            $end = $booking->date_end ? Carbon::parse($booking->date_end) : Carbon::now()->addYears(10);

            $unavailableDates[] = [
                'start' => $start->format('d-m-Y'),
                'end' => $end->format('d-m-Y')
            ];
        }

        return response()->json($unavailableDates);
    }
}