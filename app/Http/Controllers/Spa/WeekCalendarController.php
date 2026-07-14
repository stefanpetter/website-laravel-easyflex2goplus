<?php

namespace App\Http\Controllers\Spa;

use App\Http\Controllers\Controller;
use Carbon\Carbon;

class WeekCalendarController extends Controller
{
    public function index()
    {
        $week = (int) request('week', Carbon::now()->format('W'));
        $year = (int) request('year', Carbon::now()->format('o'));

        $date = Carbon::now();
        $date->setISODate($year, $week);

        $days = [];

        for ($i = 0; $i < 7; $i++) {
            $day = $date->copy()->startOfWeek()->addDays($i);
            $days[] = [
                'title' => $day->format('l'),
                'subtitle' => $day->format('d-m-Y'),
            ];
        }

        return view('spa.week-calendar', [
            'week' => $week,
            'year' => $year,
            'days' => $days,
            'previousWeek' => $date->copy()->subWeek(),
            'nextWeek' => $date->copy()->addWeek(),
            'currentWeekDate' => $date,
            'token' => request('token'),
        ]);
    }
}
