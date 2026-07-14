<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use App\Models\Room;

class RoomController extends Controller
{
    public function index() {

        return View('room.overview', [
            'rooms' => Room::all()
        ]);

    }

    public function create() {
        return View('room.create');
    }

    public function store() {

        $attributes = request()->validate([
            'name' => 'required',
            'house_id' => 'nullable',
            'floor' => 'required',
            'size' => 'numeric|nullable'
        ]);

        Room::create($attributes);

        toastr()->success('Room added successfully');
        return redirect(route('room.index'));
    }

    public function show(Room $room) {
        $room->load(['beds.bookings' => function($query) {
            $query->where('status', 'reserved')
                ->where(function ($q) {
                    $q->where('date_end', '>=', now())
                        ->orWhereNull('date_end');
                })
                ->where('date_start', '<=', now());
        }, 'beds.bookings.flexworker']);

        return View('room.show', [
            'room' => $room
        ]);
    }

    public function update(Room $room) {

        $attributes = request()->validate([
            'name' => 'required',
            'house_id' => 'nullable',
            'floor' => 'required',
            'size' => 'numeric|nullable'
        ]);

        $room->update($attributes);

        toastr()->success('Room modified successfully');
        return redirect(route('room.show', $room->id));
    }

    public function destroy(Room $room)
    {
        //Log::warning('User '. Auth::user()->name .' deleted note '. $note->id .' ('.$note->subject.')', ['note' => $note->toJson()]);
        $room->delete();

        return redirect()->route('room.index')->with('success', 'Room deleted successfully');
    }
    
}
