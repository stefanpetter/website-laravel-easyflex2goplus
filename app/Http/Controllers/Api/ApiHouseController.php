<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\House;

class ApiHouseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $flexworkers = House::orderBy('name')->where('name', 'like', '%'.request('q').'%')->get();

        $select2_array = array();
        $i = 1;
        
        $select2_array[0]['id'] = 0;
        $select2_array[0]['text'] = '-';

        foreach($flexworkers as $flexworker){

            $select2_array[$i]['id'] = $flexworker->id;
            $select2_array[$i]['text'] = $flexworker->name;
            
            $i++;
        }

        return response()->json($select2_array);
    }
}