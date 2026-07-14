<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Mail\DailyExportGenerated;
use App\Mail\DailySnelstartExportGenerated;
use App\Models\Group;
use App\Models\Booking;
use App\Models\Flexworker;
use App\Models\SnelstartExport;
use Carbon\Carbon;

class CsvController extends Controller
{
    public function createDailyCSV(){

        $columns = array('Group','House','Room','Bed','Booking','Status','Start_date','End_date','Flexworker');

        $file = fopen('php://temp/maxmemory:'. (5*1024*1024), 'r+');
        fputcsv($file, $columns, ";");

        $today = Carbon::now();
        $bookedBedsToday = Booking::where(function ($query) use ($today) {
            $query->where('date_start', '<=', $today)
                ->where(function ($q) use ($today) {
                    $q->where('date_end', '>=', $today)
                        ->orWhereNull('date_end');
                });
        })->get();

        foreach (Group::orderBy('name')->get() as $group) {

            foreach($group->houses as $house){

                foreach($house->rooms as $room){

                    foreach($room->beds as $bed){

                        $row['Group'] = $group->name;
                        $row['House'] = $house->name;
                        $row['Room'] = $room->name;
                        $row['Bed'] = $bed->name;

                        $bookingForBed = $bookedBedsToday->where('bed_id', $bed->id)->first();

                        if($bookingForBed){
                            $row['Booking'] = 'Yes';
                            $row['Status'] = Booking::statuses()[$bookingForBed->status]['name'];
                            $row['Start_date'] = $bookingForBed->date_start->format('d-m-Y');
                            $row['End_date'] = (($bookingForBed->end_date) ? $bookingForBed->end_date->format('d-m-Y') : '-');
                            $row['Flexworker'] = (($bookingForBed->flexworker) ? $bookingForBed->flexworker->name : '-');
                        } else {
                            $row['Booking'] = 'No';
                            $row['Status'] = '-';
                            $row['Start_date'] = '-';
                            $row['End_date'] = '-';
                            $row['Flexworker'] = '-';
                        }
                
                        fputcsv($file, array($row['Group'], $row['House'], $row['Room'], $row['Bed'], $row['Booking'], $row['Status'], $row['Start_date'],$row['End_date'], $row['Flexworker']), ";");
                    }
                }
            }                
        }

        rewind($file);
        $output = stream_get_contents($file);

        $fileName = (Carbon::now()->format('Y-m-d_H-i')).'_export.csv';

        // Put the content directly in file into the disk
        Storage::disk('local')->put('daily_exports/'.$fileName, $output);

        $this::sendDailyCSV();

        return response('Ok!', 200);
    }

    public static function sendDailyCSV(){

        $fileName = (Carbon::now()->format('Y-m-d_H-i')).'_export.csv';

        if(Storage::exists('daily_exports/'.$fileName)){
            $tempObject = new \stdClass();
            $tempObject->fileName = $fileName;
    
            Mail::to(explode(";", env('IMPORT_MAIL_RECEIVERS')))->send(new DailyExportGenerated($tempObject));
        }
    }

    public function createWeeklyCSV(){

        $lastWeekNumber = date( 'W', strtotime( 'last week' ) );

        $date = Carbon::now();
        $date->setISODate($date->format('Y'), $lastWeekNumber);
        $weekStart = $date->copy()->startOfWeek();
        $weekEnd = $date->copy()->endOfWeek();
        $weekArray = array();

        $bookings = Booking::with(['bed.room.house.group'])->where(function ($query) use ($weekStart, $weekEnd) {
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
        })->where('status','reserved')->get();

        $bookings->each(function ($booking) {
            $booking->group_id = ($booking->bed->room->house->group)->id ?? null;
        });

        foreach(Group::all() as $group){
            $filteredBookings = $bookings->where('group_id', $group->id)->groupBy('bed_id')->all();

            if(count($filteredBookings) > 0) {

                $daysToTax = 0;

                $columns = array('Group','House','Room','Bed','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
                $file = fopen('php://temp/maxmemory:'. (5*1024*1024), 'r+');
                fputcsv($file, $columns, ";");
                
                foreach($group->houses as $house){

                    foreach($house->rooms as $room){
    
                        foreach($room->beds as $bed){

                            if(array_key_exists($bed->id, $filteredBookings)){

                                foreach($filteredBookings[$bed->id] as $booking){

                                    $row['Group'] = $group->name;
                                    $row['House'] = $house->name;
                                    $row['Room'] = $room->name;
                                    $row['Bed'] = $bed->name;

                                    for($i = 0; $i < 7; $i++){

                                        if($booking->date_start <= $weekStart->copy()->addDays($i) && !$booking->date_end){
                                            $row[$columns[($i + 4)]] = ($booking->flexworker->name ?? '-');
                                            $daysToTax++;
                                        }
                                        elseif($booking->date_start <= $weekStart->copy()->addDays($i) && $booking->date_end >= $weekStart->copy()->addDays($i)){
                                            $row[$columns[($i + 4)]] = ($booking->flexworker->name ?? '-');
                                            $daysToTax++;
                                        } else {
                                            $row[$columns[($i + 4)]] = 'No booking';
                                        }
            
                                    }

                                    fputcsv($file, array($row['Group'], $row['House'], $row['Room'], $row['Bed'], $row['Monday'], $row['Tuesday'], $row['Wednesday'],$row['Thursday'], $row['Friday'], $row['Saturday'], $row['Sunday']), ";");

                                }
                            }
                        }
                    }
                }      

                fputcsv($file, array('', '', '', '', '', '', '', '', '', '', ''), ";");
                fputcsv($file, array('', '', '', '', '', '', '', '', '', 'Totaal:', ('€ '.number_format(($daysToTax * $group->tourist_tax), 2, ",", "."))), ";");

                rewind($file);
                $output = stream_get_contents($file);
        
                $fileName = (Carbon::now()->format('Y-W')).'_'.Str::slug($group->name).'_export.csv';
        
                // Put the content directly in file into the disk
                Storage::disk('local')->put('weekly_exports/'.$fileName, $output);
            }
        }

        return response('Ok!', 200);
    }

    public function createDailySnelstartCSV(){

        $today = Carbon::now();

        $bookings = Booking::with(['bed.room.house.group'])->where(function ($query) use ($today) {
            $query->where('date_start', '<=', $today)
                ->where(function ($q) use ($today) {
                    $q->where('date_end', '>=', $today)
                        ->orWhereNull('date_end');
                });
        })->where('status','reserved')->get();

        $bookings->each(function ($booking) {
            $booking->group_id = ($booking->bed->room->house->group)->id ?? null;
        });

        $columns = array('Date','House', 'Kostenplaats', 'Room','Bed','BookingID','Flexworker','SnelstartID','PricePerWeek','PricePerDay');
        $file = fopen('php://temp/maxmemory:'. (5*1024*1024), 'r+');
        fputcsv($file, $columns, ";");

        $totalPricePerDay = 0;
        $totalPricePerWeek = 0;
        $bookingCount = 0;

        foreach($bookings as $booking) {
            // Skip if bed relationship is missing
            if (!$booking->bed || !$booking->bed->room || !$booking->bed->room->house) {
                continue;
            }

            $row['Date'] = $today->format('d-m-Y');
            $row['House'] = $booking->bed->room->house->name ?? '-';
            $row['Kostenplaats'] = $booking->bed->room->house->grootboek_nr ?? '-';
            $row['Room'] = $booking->bed->room->name ?? '-';
            $row['Bed'] = $booking->bed->name ?? '-';
            $row['BookingID'] = $booking->id ?? '-';
            $row['Flexworker'] = $booking->flexworker?->name ?? '-';
            $row['SnelstartID'] = $booking->flexworker?->snelstart_id ?? '-';
            $row['PricePerWeek'] = number_format(($booking->bed->room->house->price ?? 0), 2, ",", ".");
            
            $pricePerWeek = $booking->bed->room->house->price ?? 0;
            $pricePerDay = $pricePerWeek / 7;
            $row['PricePerDay'] = number_format($pricePerDay, 2, ",", ".");
            
            $totalPricePerDay += $pricePerDay;
            $totalPricePerWeek += $pricePerWeek;
            $bookingCount++;

            fputcsv($file, array($row['Date'],$row['House'], $row['Kostenplaats'], $row['Room'], $row['Bed'], $row['BookingID'], $row['Flexworker'], $row['SnelstartID'], $row['PricePerWeek'], $row['PricePerDay']), ";");
        }

        rewind($file);
        $output = stream_get_contents($file);

        $fileName = (Carbon::now()->format('Y-m-d')).'_snelstart_export.csv';

        // Put the content directly in file into the disk
        Storage::disk('local')->put('daily_snelstart_exports/'.$fileName, $output);

        // Save export record to database
        SnelstartExport::create([
            'filename' => $fileName,
            'export_date' => $today->toDateString(),
            'total_price' => $totalPricePerDay,
            'total_price_per_week' => $totalPricePerWeek,
            'booking_count' => $bookingCount
        ]);

        if(Storage::exists('daily_snelstart_exports/'.$fileName)){
            $tempObject = new \stdClass();
            $tempObject->fileName = $fileName;
            
            // Get flexworkers without snelstart_id
            $tempObject->flexworkersWithoutSnelstartId = Flexworker::whereNull('snelstart_id')
                ->orWhere('snelstart_id', '')
                ->get(['id', 'first_name', 'last_name', 'initials', 'email']);
    
            Mail::to(explode(";", env('IMPORT_MAIL_RECEIVERS')))->send(new DailySnelstartExportGenerated($tempObject));
        }

        return response('Ok!', 200);
    }
}
