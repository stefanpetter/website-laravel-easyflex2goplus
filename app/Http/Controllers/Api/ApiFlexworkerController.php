<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Flexworker;

class ApiFlexworkerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $flexworkers = Flexworker::where('first_name', 'like', '%'.request('q').'%')->orWhere('last_name', 'like', '%'.request('q').'%')->get();

        $select2_array = array();
        $i = 0;

        foreach($flexworkers as $flexworker){

            $select2_array[$i]['id'] = $flexworker->id;
            $select2_array[$i]['text'] = $flexworker->last_name . ', '. $flexworker->initials .' ('. $flexworker->first_name.') (Rel: '.$flexworker->relation_id.')';
            
            $i++;
        }

        return response()->json($select2_array);
    }
}