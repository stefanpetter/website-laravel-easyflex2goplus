<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

use App\Http\Controllers\BookingController;

use App\Models\Booking;
use App\Models\House;
use App\Models\Bed;
use App\Models\Flexworker;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function index(Request $request): View
    {
        $today = Carbon::now();
        //$twoWeeks = Carbon::now()->subDays(14);
        $arrivingBookings = Booking::where('status', 'pendingarrival')->whereNotNull('date_end')->whereBetween('date_end', [$today, Carbon::now()->addWeeks(2)])->get();
        $endingBookings = Booking::whereIn('status', array('reserved', 'reservedonrequest'))->whereNotNull('date_end')->whereBetween('date_end', [$today, Carbon::now()->addWeeks(2)])->get();
        $totalSnfBeds = House::where('status', 'available')->sum('snf_beds');
        $totalBeds = Bed::whereHas('room.house', function($query) {
            $query->where('status', 'available');
        })->count();
        $bookedBedsToday = Booking::where(function ($query) use ($today) {
            $query->where('date_start', '<=', $today)
                ->whereIn('status', array('reserved', 'reservedonrequest'))
                ->where(function ($q) use ($today) {
                    $q->where('date_end', '>=', $today)
                        ->orWhereNull('date_end');
                });
        })->count();

        $totalFlexworkers = Flexworker::where('status', 'working')->count();

        $bookingsForDoughnutChart = BookingController::getBookingsForDoughnutChart();

        //dd($bookingsForDoughnutChart);

        //dd($bookedBedsToday);
        
        return view('dashboard', [
            'totalSnfBeds' => $totalSnfBeds,
            'totalBeds' => $totalBeds,
            'bookedBedsToday' => $bookedBedsToday,
            'arrivingBookings' => $arrivingBookings,
            'endingBookings' => $endingBookings,
            'totalFlexworkers' => $totalFlexworkers,
            'bookingsForDoughnutChart' => $bookingsForDoughnutChart
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
