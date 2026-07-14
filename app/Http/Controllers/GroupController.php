<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use App\Models\Group;

class GroupController extends Controller
{
    public function index() {

        return View('group.overview', [
            'groups' => Group::all()
        ]);

    }

    public function create() {
        return View('group.create');
    }

    public function store() {

        $attributes = request()->validate([
            'name' => 'required|unique:groups',
            'tourist_tax' => 'numeric|nullable'
        ]);

        Group::create($attributes);

        toastr()->success('Group added successfully');
        return redirect(route('group.index'));
    }

    public function show(Group $group) {

        return View('group.show', [
            'group' => $group
        ]);
    }

    public function update(Group $group) {

        $attributes = request()->validate([
            'name' => ['required', Rule::unique('groups', 'name')->ignore($group->id)],
            'tourist_tax' => 'numeric|nullable'
        ]);

        $group->update($attributes);

        toastr()->success('Group modified successfully');
        return redirect(route('group.show', $group->id));
    }

    public function destroy(Group $group)
    {
        //Log::warning('User '. Auth::user()->name .' deleted note '. $note->id .' ('.$note->subject.')', ['note' => $note->toJson()]);
        $group->delete();

        return redirect()->route('group.index')->with('success', 'Group deleted successfully');
    }
    
}
