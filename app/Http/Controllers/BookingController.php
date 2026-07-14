<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\House;
use App\Models\Booking;
use App\Models\Bed;
use App\Models\Flexworker;

use Carbon\Carbon;
use stdClass;

class BookingController extends Controller
{
    public function index() {

        $lastWeekNumber = date( 'W', strtotime( 'last week' ) );
        $date = Carbon::now();
        $date->setISODate($date->format('Y'), $lastWeekNumber);
        $weekStart = $date->copy()->startOfWeek();
        $weekEnd = $date->copy()->endOfWeek();

        $previousWeekBookings = Booking::whereBetween('created_at', [$weekStart, $weekEnd])->get();
        $allBookings =  Booking::all();

        return View('booking.overview', [
            'previousWeekBookings' => $previousWeekBookings,
            'bookings' => $allBookings
        ]);
    }

    public function conflicts() {
        // Get all bookings with their relationships
        $bookings = Booking::with(['bed.room.house', 'flexworker'])
            ->orderBy('bed_id')
            ->orderBy('date_start')
            ->get();

        // Group bookings by bed_id
        $bookingsByBed = $bookings->groupBy('bed_id');

        // Find conflicts: beds with overlapping date ranges
        $conflicts = collect();

        foreach ($bookingsByBed as $bedId => $bedBookings) {
            if ($bedBookings->count() < 2) {
                continue; // Skip beds with only one booking
            }

            $conflictingBookings = collect();

            // Compare each booking with every other booking for this bed
            foreach ($bedBookings as $i => $booking1) {
                $hasConflict = false;

                foreach ($bedBookings as $j => $booking2) {
                    if ($i >= $j) continue; // Skip same booking and already compared pairs

                    // Check if dates overlap
                    $start1 = $booking1->date_start;
                    $end1 = $booking1->date_end ?: Carbon::parse('9999-12-31');
                    $start2 = $booking2->date_start;
                    $end2 = $booking2->date_end ?: Carbon::parse('9999-12-31');

                    // Overlap exists if: start1 <= end2 AND end1 >= start2
                    if ($start1 <= $end2 && $end1 >= $start2) {
                        $hasConflict = true;
                        if (!$conflictingBookings->contains('id', $booking2->id)) {
                            $conflictingBookings->push($booking2);
                        }
                    }
                }

                if ($hasConflict && !$conflictingBookings->contains('id', $booking1->id)) {
                    $conflictingBookings->push($booking1);
                }
            }

            // If we found conflicts for this bed, add them to results
            if ($conflictingBookings->count() > 1) {
                $conflicts->put($bedId, $conflictingBookings->sortBy('date_start'));
            }
        }

        return View('booking.conflicts', [
            'conflicts' => $conflicts
        ]);
    }

    public function weekview() {

        $weeknr = request('week') ?? Carbon::now()->format('W');
        
        // Smart year defaulting: if no year provided, use current ISO year
        // BUT if requested week is high (>26) and current week is low (<26), assume previous year
        if (!request('year')) {
            $currentWeek = Carbon::now()->format('W');
            $currentYear = Carbon::now()->format('o');
            
            // If requesting high week number while we're in a low week, use previous year
            if ($weeknr > 26 && $currentWeek <= 26) {
                $year = $currentYear - 1;
            } else {
                $year = $currentYear;
            }
        } else {
            $year = request('year');
        }

        $date = Carbon::now();
        $date->setISODate($year, $weeknr); // Use the year parameter
        $weekStart = $date->copy()->startOfWeek();
        $weekEnd = $date->copy()->endOfWeek();
        $weekArray = array();

        $bookings = Booking::with(['flexworker', 'bed.room.house'])
            ->where(function ($query) use ($weekStart, $weekEnd) {
            $query->whereBetween('date_start', [$weekStart, $weekEnd]) // Starts within the week
                ->orWhereBetween('date_end', [$weekStart, $weekEnd]) // Ends within the week
                ->orWhere(function ($q) use ($weekStart) {
                    $q->where('date_start', '<', $weekStart) // Started before this week
                      ->whereNull('date_end'); // Still ongoing
                })
                ->orWhere(function ($q) use ($weekStart, $weekEnd) {
                    $q->where('date_start', '<', $weekStart) // Started before this week
                      ->where('date_end', '>=', $weekStart); // Ends after or within the week
                });
            })->get();

        for($i=0;$i<7;$i++){
            $dateData = $date->startOfWeek()->addDays($i);
            $weekArray[$i]['title'] = $dateData->format('l');
            $weekArray[$i]['subtitle'] = $dateData->format('(d-m)');
            $weekArray[$i]['date'] = $dateData->format('Y-m-d');;
        }

        $filterHouse = null;
        $filterGroup = null;
        $filterFlexworker = null;
        $filterHouses = null;
        $filterRooms = null;
        $filterBeds = null;

        if(request('house_id') && request('house_id') > 0){
            $filterHouse = House::find(request('house_id'));
            $filterHouses = House::where('id', request('house_id'))->get()->pluck('id')->toArray();
        } 
        elseif(request('group_id') && request('group_id') > 0){
            $filterGroup = Group::find(request('group_id'));
            $filterHouses = Group::find(request('group_id'))->houses()->pluck('id')->toArray();
        }
        elseif(request('flexworker_id') && request('flexworker_id') > 0){
            $filterFlexworker = Flexworker::find(request('flexworker_id'));
            foreach($bookings as $booking){
                if($booking->flexworker_id == request('flexworker_id')){
                    $filterHouses[] = $booking->bed->room->house->id ?? 0;
                    $filterRooms[] = $booking->bed->room->id ?? 0;
                    $filterBeds[] = $booking->bed->id ?? 0;
                }
            }
        }

        // Calculate proper week numbers with year wrapping
        $dateMinus1 = $date->copy()->subWeek();
        $dateMinus2 = $date->copy()->subWeeks(2);
        $datePlus1 = $date->copy()->addWeek();
        $datePlus2 = $date->copy()->addWeeks(2);
        
        $weekMinus1 = $dateMinus1->format('W');
        $weekMinus2 = $dateMinus2->format('W');
        $weekPlus1 = $datePlus1->format('W');
        $weekPlus2 = $datePlus2->format('W');
        
        $yearMinus1 = $dateMinus1->format('o');
        $yearMinus2 = $dateMinus2->format('o');
        $yearPlus1 = $datePlus1->format('o');
        $yearPlus2 = $datePlus2->format('o');

        //dd($bookings->groupBy('bed_id'));
        //dd($weekArray);

        return View('booking.weekview', [
            'now' => $date,
            'weekArray' => $weekArray,
            'houses' => House::with(['rooms.beds'])->orderBy('name')->get(),
            'bookings' => $bookings->groupBy('bed_id'),
            'filterHouses' => $filterHouses,
            'filterRooms' => $filterRooms,
            'filterBeds' => $filterBeds,
            'filterHouse' => $filterHouse,
            'filterGroup' => $filterGroup,
            'filterFlexworker' => $filterFlexworker,
            'currentWeek' => $weeknr,
            'currentYear' => $year,
            'weekMinus1' => $weekMinus1,
            'weekMinus2' => $weekMinus2,
            'weekPlus1' => $weekPlus1,
            'weekPlus2' => $weekPlus2,
            'yearMinus1' => $yearMinus1,
            'yearMinus2' => $yearMinus2,
            'yearPlus1' => $yearPlus1,
            'yearPlus2' => $yearPlus2
        ]);

    }

    public function create() {

        $bed = '';
        if(request('bed_id')){
            $bed = Bed::find(request('bed_id'));
        }

        return View('booking.create', [
            'bed' => $bed
        ]);
    }

    public function store() {

        $attributes = request()->validate([
            'bed_id' => 'integer|required',
            'flexworker_id' => 'integer|nullable',
            'date_start' => 'date|required',
            'date_end' => 'date|nullable',
            'status' => 'string|required'
        ]);

        ($attributes['date_start']) ? $attributes['date_start'] = Carbon::createFromFormat('d-m-Y', $attributes['date_start'])->format('Y-m-d') : null;
        ($attributes['date_end']) ? $attributes['date_end'] = Carbon::createFromFormat('d-m-Y', $attributes['date_end'])->format('Y-m-d') : null;

        // Validate bed availability
        if (!$this->isBedAvailable($attributes['bed_id'], $attributes['date_start'], $attributes['date_end'])) {
            toastr()->error('This bed is not available for the selected date range. Please choose another bed or date range.');
            return back()->withInput();
        }

        Booking::create($attributes);

        if(request('resume_booking')){
            Booking::create([
                'bed_id' => $attributes['bed_id'],
                'flexworker_id' => $attributes['flexworker_id'],
                'date_start' => Carbon::createFromFormat('Y-m-d', $attributes['date_end'])->addDay()->format('Y-m-d'),
                'date_end' => null,
                'status' => 'reserved'
            ]);
        }

        toastr()->success('Booking added successfully');
        return redirect(route('booking.index'));
    }

    public function createVacation(Booking $booking) {

        return View('booking.createVacation', [
            'booking' => $booking
        ]);
    }

    public function storeVacation(Booking $booking) {

        $oldBookingEndDate = ($booking->date_end) ? $booking->date_end : null;

        $attributes = request()->validate([
            'date_start' => 'date|required',
            'date_end' => 'date|required',
        ]);

        $vacationStart = Carbon::createFromFormat('d-m-Y', $attributes['date_start'])->format('Y-m-d');
        $vacationEnd = Carbon::createFromFormat('d-m-Y', $attributes['date_end'])->format('Y-m-d');

        // Validate that vacation period doesn't conflict with other flexworkers' bookings on this bed
        // We allow overlap with the same flexworker's bookings as we'll be splitting/merging them
        $conflictingBooking = Booking::where('bed_id', $booking->bed_id)
            ->where('flexworker_id', '!=', $booking->flexworker_id) // Only check other flexworkers
            ->where(function ($q) use ($vacationStart, $vacationEnd) {
                $q->where(function ($sub) use ($vacationStart, $vacationEnd) {
                    $sub->where('date_start', '<=', $vacationEnd)
                        ->where(function ($inner) use ($vacationStart) {
                            $inner->whereNull('date_end')
                                  ->orWhere('date_end', '>=', $vacationStart);
                        });
                });
            })
            ->exists();

        if ($conflictingBooking) {
            toastr()->error('Cannot create vacation: bed is already booked during this period by another reservation.');
            return back()->withInput();
        }

        $booking->date_end = Carbon::parse($vacationStart)->subDay()->format('Y-m-d');
        $booking->save();

        $vacationBooking = new Booking([
            'bed_id' => $booking->bed_id,
            'flexworker_id' => $booking->flexworker_id,
            'date_start' => $vacationStart,
            'date_end' => $vacationEnd,
            'status' => 'vacation'
        ]);
        $vacationBooking->save();

        $continueBooking = new Booking([
            'bed_id' => $booking->bed_id,
            'flexworker_id' => $booking->flexworker_id,
            'date_start' => Carbon::createFromFormat('d-m-Y', $attributes['date_end'])->addDay()->format('Y-m-d'),
            'date_end' => $oldBookingEndDate,
            'status' => 'reserved'
        ]);
        $continueBooking->save();

        toastr()->success('Vacation booking added successfully');
        return redirect(route('booking.index'));
    }

    public function createRelocation(Booking $booking) {

        return View('booking.createRelocation', [
            'booking' => $booking
        ]);
    }

    public function storeRelocation(Booking $booking) {

        $attributes = request()->validate([
            'date_start' => 'date|required',
            'bed_id' => 'integer|required|gt:0',
        ]);

        $relocationStart = Carbon::createFromFormat('d-m-Y', $attributes['date_start'])->format('Y-m-d');
        $relocationEnd = $booking->date_end; // Use the original booking's end date

        // Validate that the new bed is available for the entire remaining period
        if (!$this->isBedAvailable($attributes['bed_id'], $relocationStart, $relocationEnd)) {
            toastr()->error('Cannot relocate: the new bed is not available for the requested period. Please choose another bed or date.');
            return back()->withInput();
        }

        $booking->date_end = Carbon::parse($relocationStart)->subDay()->format('Y-m-d');
        $booking->save();

        $reservedOnrequest = Booking::where('flexworker_id', $booking->flexworker_id)
            ->where('status', 'reservedonrequest')
            ->whereNull('date_end')
            ->first();

        if($reservedOnrequest){
            $reservedOnrequest->date_end = Carbon::parse($relocationStart)->subDay()->format('Y-m-d');
            $reservedOnrequest->save();
        }

        $continueBooking = new Booking([
            'bed_id' => $attributes['bed_id'],
            'flexworker_id' => $booking->flexworker_id,
            'date_start' => $relocationStart,
            'date_end' => $relocationEnd,
            'status' => 'reserved'
        ]);
        $continueBooking->save();

        toastr()->success('Flexworker relocation added successfully');
        return redirect(route('booking.index'));
    }

    public function show(Booking $booking) {

        $canEditStartDate = (Carbon::now()->isBefore($booking->date_start)) ? true : false;
        $canEditEndDate = true;
        if($booking->date_end) {
            $futureBooking = Booking::where('bed_id', $booking->bed_id)
            ->where('date_start', '>', $booking->date_end)
            ->orderBy('date_start')
            ->first();
        } else {
            $futureBooking = null;
        }

        if($futureBooking && $futureBooking->flexworker_id != $booking->flexworker_id) {
            $canEditEndDate = false;
        } 

        return View('booking.show', [
            'booking' => $booking,
            'canEditStartDate'  => $canEditStartDate,
            'canEditEndDate' => $canEditEndDate,
            'futureBooking' => $futureBooking
        ]);
    }

    public function update(Booking $booking) {

        $attributes = request()->validate([
            'flexworker_id' => 'integer|nullable',
            'date_start' => 'date|required',
            'date_end' => 'date|nullable',
            'status' => 'string|required'
        ]);

        $attributes['date_start'] = Carbon::createFromFormat('d-m-Y', $attributes['date_start'])->format('Y-m-d');
        ($attributes['date_end']) ? $attributes['date_end'] = Carbon::createFromFormat('d-m-Y', $attributes['date_end'])->format('Y-m-d') : null;

        if($attributes['date_end'] && $booking->date_end) {
            $futureBooking = Booking::where('bed_id', $booking->bed_id)
            ->where('date_start', '>', $booking->date_end)
            ->orderBy('date_start')
            ->first();

            $newDateEnd = Carbon::createFromFormat('Y-m-d', $attributes['date_end']);

            if($futureBooking && $futureBooking->date_start <= $newDateEnd) {
                $futureBooking->date_start = $newDateEnd->addDay()->format('Y-m-d');
                $futureBooking->save();
            }
        }

        $booking->update($attributes);

        toastr()->success('Booking modified successfully');
        return redirect(route('booking.show', $booking->id));
    }

    public function destroy(Booking $booking)
    {
        //Log::warning('User '. Auth::user()->name .' deleted note '. $note->id .' ('.$note->subject.')', ['note' => $note->toJson()]);
        $booking->delete();

        return redirect()->route('booking.index')->with('success', 'Booking deleted successfully');
    }

    public static function getBookingsForDoughnutChart() {
        
        $today = Carbon::now();

        $activeBookings = Booking::where(function ($query) use ($today) {
                                $query->where('date_start', '<=', $today)
                                    ->where(function ($q) use ($today) {
                                        $q->where('date_end', '>=', $today)
                                            ->orWhereNull('date_end');
                                    });
                            })->get();

        $dataArray = array('labels' => array(), 'datasets' => array());
        $object = new stdClass();
        $backgroundColors = array();
        $data = array();
        $i = 1;

        $availableBeds = Bed::whereNotIn('id', $activeBookings->pluck('bed_id')->toArray())
                            ->whereHas('room.house', function($query) {
                                $query->where('status', 'available');
                            })->count();
        $chartColors = array("#dc3545","#fd7e14","#ffc107","#28a745","#007bff","#6f42c1","#6c757d");

        $dataArray['labels'][] = "Available";
        $data[] = $availableBeds;
        $backgroundColors[] = '#3ac47d';

        foreach($activeBookings->groupBy('status') as $status => $activeBookingsForGroup) {

            $object = new stdClass();

            $dataArray['labels'][] = Booking::statuses()[$status]['name'];
            $data[] = $activeBookingsForGroup->count();

            switch($status){
                case 'available':
                    $backgroundColors[] = '#3ac47d';
                    break;
                case 'reserved':
                    $backgroundColors[] = '#3f6ad8';
                    break;
                case 'reservedonrequest':
                    $backgroundColors[] = '#16aaff';
                    break;
                case 'pendingarrival':
                    $backgroundColors[] = '#f7b924';
                    break;
                case 'vacation':
                    $backgroundColors[] = '#794c8a';
                    break;
                case 'maintenance':
                    $backgroundColors[] = '#6c757d';
                    break;
                case 'renovation':
                    $backgroundColors[] = '#6c757d';
                    break;
                case 'blocked':
                    $backgroundColors[] = '#d92550';
                    break;
                default:
                    $backgroundColors[] = $chartColors[$i];
            }
            $i++;
        }

        $object->data = $data;
        $object->backgroundColor = $backgroundColors;

        $dataArray['datasets'][] = $object;

        return json_encode($dataArray);
    }

    public function checkFutureBookings(Request $request)
    {
        $request->validate([
            'bed_id' => 'required|integer|exists:beds,id',
            'date_end' => 'required|date',
        ]);

        // Ensure the date_end is in the correct format
        $formattedDateEnd = Carbon::createFromFormat('d-m-Y', $request->date_end)->format('Y-m-d');

        $futureBookings = Booking::where('bed_id', $request->bed_id)
            ->where('date_start', '>', $formattedDateEnd)
            ->exists();

        return response()->json([
            'has_future_bookings' => $futureBookings
        ]);
    }

    /**
     * Check if a bed is available for a given date range
     */
    private function isBedAvailable($bedId, $dateStart, $dateEnd = null, $excludeBookingId = null)
    {
        $query = Booking::where('bed_id', $bedId)
            ->where(function ($q) use ($dateStart, $dateEnd) {
                // Check for any overlap with existing bookings
                $q->where(function ($sub) use ($dateStart, $dateEnd) {
                    // Existing booking starts before or during requested period
                    $sub->where('date_start', '<=', $dateEnd ?: '9999-12-31')
                        ->where(function ($inner) use ($dateStart) {
                            // And it either has no end date (ongoing) or ends during/after requested start
                            $inner->whereNull('date_end')
                                  ->orWhere('date_end', '>=', $dateStart);
                        });
                });
            });

        // Exclude the current booking when editing
        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return !$query->exists();
    }
}
