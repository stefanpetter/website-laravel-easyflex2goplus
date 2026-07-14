<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\User;

class UserController extends Controller
{
    public function index() {
        $users = User::all();  
        return View('user.overview', compact('users'));
    }

    public function create()
    {
        return view('user.create');
    }

    public function store() {

        //dd(request());

        $attributes = request()->validate([
            'name' => 'required',
            'email' => 'required|unique:users',
            'password' => 'required|min:8'
        ]);

        $attributes['password'] = Hash::make($attributes['password']);
        $user = User::create($attributes);

        Log::info('User '. Auth::user()->name .' created user '. $user->id .' ('.$user->name.')');
        return redirect()->route('user.index')->with('success', 'User added successfully');
    }

    public function show($id)
    {
        $user = User::find($id);
        return view('user.show', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|max:255',
        ]);

        $user = User::find($id);
        $oldObjectJson = $user->toJson();
        $user->update(array_filter($request->except(['password', 'is_admin'])));

        if($request['password']){
            $user->password = Hash::make($request['password']);
        }

        if(isset($request['is_admin']) && $request['is_admin'] == 'on'){
            $user->is_admin = 1;
        } else{
            $user->is_admin = 0;
        }

        $user->save();

        $newObjectJson = $user->toJson();

        Log::info('User '. Auth::user()->name .' modified user '. $user->id .' ('.$user->name.')', ['old' => $oldObjectJson, 'new' => $newObjectJson]);
        return redirect()->route('user.show', $id)->with('success', 'User modified successfully');
    }

    public function destroy($id)
    {
        $user = User::find($id);
        Log::warning('User '. Auth::user()->name .' deleted user '. $user->id .' ('.$user->name.')', ['user' => $user->toJson()]);
        $user->relations()->detach();
        $user->delete();
        return redirect()->route('user.index')->with('success', 'User deleted successfully');
    }
}