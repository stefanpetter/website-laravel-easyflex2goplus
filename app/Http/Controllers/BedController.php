<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use App\Models\Bed;

class BedController extends Controller
{
    public function index() {

        return View('bed.overview', [
            'beds' => Bed::all()
        ]);

    }

    public function create() {
        return View('bed.create');
    }

    public function store() {

        $attributes = request()->validate([
            'name' => 'required',
            'room_id' => 'nullable'
        ]);

        Bed::create($attributes);

        toastr()->success('Bed added successfully');
        return redirect(route('bed.index'));
    }

    public function show(Bed $bed) {

        // Get all bookings sorted by date (newest first)
        $bookings = $bed->bookings()
            ->with('flexworker')
            ->orderBy('date_start', 'desc')
            ->get();

        // Get active booking (start date in past, and either no end date or end date in future)
        $activeBooking = $bed->bookings()
            ->with('flexworker')
            ->whereDate('date_start', '<=', now())
            ->where(function($query) {
                $query->whereNull('date_end')
                      ->orWhereDate('date_end', '>=', now());
            })
            ->first();

        return View('bed.show', [
            'bed' => $bed,
            'bookings' => $bookings,
            'activeBooking' => $activeBooking
        ]);
    }

    public function update(Bed $bed) {

        //dd(request());

        $attributes = request()->validate([
            'name' => 'required',
            'room_id' => 'nullable'
        ]);

        $bed->update($attributes);

        toastr()->success('Bed modified successfully');
        return redirect(route('bed.show', $bed->id));
    }

    public function destroy(Bed $bed)
    {
        //Log::warning('User '. Auth::user()->name .' deleted note '. $note->id .' ('.$note->subject.')', ['note' => $note->toJson()]);
        $bed->delete();

        return redirect()->route('bed.index')->with('success', 'Bed deleted successfully');
    }
    
}
