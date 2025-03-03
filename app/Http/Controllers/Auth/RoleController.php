<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        return view('auth.roles.index', compact('roles'));
    }

    public function show(Role $role)
{
    return view('auth.roles.show', compact('role'));
}
    public function create()
    {
        $permissions = Permission::all();
        return view('auth.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        dd($request);
        $role = Role::create($request->only('name'));
        $role->syncPermissions($request->input('permissions'));
        return redirect()->route('roles.index');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all();
        return view('auth.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $role->update($request->only('name'));
        $role->syncPermissions($request->input('permissions'));
        return redirect()->route('roles.index');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return redirect()->route('roles.index');
    }
}
