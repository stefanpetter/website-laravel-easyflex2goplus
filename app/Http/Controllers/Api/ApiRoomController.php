<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;

class ApiRoomController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $availableRooms = Room::all(); 
        $select2_array = array();
        $i = 0;

        foreach($availableRooms as $room){

            $search = request('q');
            $text = ($room->house->name ?? 'House').' | '.$room->name;

            if(strlen($search) > 0){
                if(str_contains(strtolower($text), $search)){
                    $select2_array[$i]['id'] = $room->id;
                    $select2_array[$i]['text'] = $text;
                        
                    $i++;
                }
            } else {
                $select2_array[$i]['id'] = $room->id;
                $select2_array[$i]['text'] = $text;
                    
                $i++;
            }
        }

        return response()->json($select2_array);
    }
}