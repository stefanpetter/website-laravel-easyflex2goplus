<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Group;
use App\Models\House;
use App\Models\Room;
use App\Models\Bed;
use App\Models\Flexworker;
use App\Models\User;

class SearchController extends Controller
{
    public function index() {

        $groups = Group::where('name', 'like', '%'.request('q').'%')->get();
        $houses = House::where('name', 'like', '%'.request('q').'%')->get();
        $rooms = Room::where('name', 'like', '%'.request('q').'%')->get();
        $beds = Bed::where('name', 'like', '%'.request('q').'%')->get();
        $flexworkers = Flexworker::where('first_name', 'like', '%'.request('q').'%')->orWhere('last_name', 'like', '%'.request('q').'%')->get();

        if(Auth::user()->is_admin){
            $users = User::where('name', 'like', '%'.request('q').'%')->get();
        } else {
            $users = array();
        }

        $request_count = (count($groups) + count($houses) + count($rooms) + count($beds) + count($flexworkers) + count($users));

        return View('search.overview', [
            'groups' => $groups,
            'houses' => $houses,
            'rooms' => $rooms,
            'beds' => $beds,
            'flexworkers' => $flexworkers,
            'users' => $users,
            'request_count' => $request_count
        ]);
    }
}