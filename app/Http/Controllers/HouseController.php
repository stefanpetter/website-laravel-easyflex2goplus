<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use App\Models\House;
use App\Models\Booking;
use App\Models\Bed;
use Carbon\Carbon;

class HouseController extends Controller
{
    public function index() {

        $today = Carbon::now();
        $bookedBedsToday = Booking::where(function ($query) use ($today) {
            $query->where('date_start', '<=', $today)
                ->whereIn('status', array('reserved', 'reservedonrequest'))
                ->where(function ($q) use ($today) {
                    $q->where('date_end', '>=', $today)
                        ->orWhereNull('date_end');
                });
        })->get()->pluck('bed_id')->toArray();

        $houses = House::with(['rooms' => function ($query) {
            $query->with('beds');
        }])->get();

        $houses->each(function ($house) use ($bookedBedsToday) {

            $beds = $house->rooms->flatMap->beds;
            $house->total_beds = count($beds);
            $house->booked_beds = 0;

            foreach($beds as $bed){
                if(in_array($bed->id, $bookedBedsToday)){
                    $house->booked_beds++;
                }
            }
        });

        return View('house.overview', [
            'houses' => $houses
        ]);

    }

    public function create() {
        return View('house.create');
    }

    public function store() {

        $attributes = request()->validate([
            'group_id' => 'nullable',
            'name' => 'required|unique:houses',
            'status' => 'required',
            'snf_beds' => 'integer|nullable',
            'snf_status' => 'nullable',
            'price' => 'numeric|nullable',
            'grootboek_nr' => 'nullable',
            'description' => 'nullable',
            'gbo' => 'numeric|nullable'
        ]);

        House::create($attributes);

        toastr()->success('House added successfully');
        return redirect(route('house.index'));
    }

    public function show(House $house) {
        $house->load(['rooms.beds.bookings' => function($query) {
            $query->where('status', 'reserved')
                ->where(function ($q) {
                    $q->where('date_end', '>=', now())
                        ->orWhereNull('date_end');
                })
                ->where('date_start', '<=', now());
        }, 'rooms.beds.bookings.flexworker']);

        return View('house.show', [
            'house' => $house
        ]);
    }

    public function update(House $house) {

        $attributes = request()->validate([
            'group_id' => 'nullable',
            'name' => ['required', Rule::unique('houses', 'name')->ignore($house->id)],
            'status' => 'required',
            'snf_beds' => 'integer|nullable',
            'snf_status' => 'nullable',
            'price' => 'numeric|nullable',
            'grootboek_nr' => 'nullable',
            'description' => 'nullable',
            'gbo' => 'numeric|nullable'
        ]);

        $house->update($attributes);

        toastr()->success('House modified successfully');
        return redirect(route('house.show', $house->id));
    }

    public function destroy(House $house)
    {
        //Log::warning('User '. Auth::user()->name .' deleted note '. $note->id .' ('.$note->subject.')', ['note' => $note->toJson()]);
        $house->delete();

        return redirect()->route('house.index')->with('success', 'House deleted successfully');
    }
    
}
