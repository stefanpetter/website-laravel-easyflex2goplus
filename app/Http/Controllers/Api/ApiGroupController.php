<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;

class ApiGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $groups = Group::orderBy('name')
            ->where('name', 'like', '%'.request('q').'%')
            ->select('*', 'name as text')
            ->get();

        return response()->json($groups);
    }
}