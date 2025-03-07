<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Validator;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        // Start the query
        $roles = Role::query();

        // Search by name
        if ($request->has('search')) {
            $search = $request->input('search');
            $roles->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            });
        }

        // Filter by permission
        if ($request->has('permission') && $request->input('permission') !== '' && $request->input('permission') != null) {
            $permission = $request->input('permission');
            $roles->whereHas('permissions', function ($query) use ($permission) {
                $query->where('name', $permission);
            });
        }

        // Sort by column
        if ($request->has('sort')) {
            $sort = $request->input('sort');
            $direction = $request->input('direction', 'asc');
            $roles->orderBy($sort, $direction);
        }

        // Paginate the results
        $roles = $roles->paginate(10); // Use paginate() instead of get()

        // Get all roles for the filter dropdown
        $permissions = Permission::all();

        return view('auth.roles.index', compact('roles', 'permissions'));
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $role = Role::create($request->all());
        $role->permissions()->sync($request->permissions);

        return response()->json(['message' => 'Role created successfully']);
    }
    public function update(Request $request, Role $role)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $role->update($request->all());
        $role->permissions()->sync($request->permissions);
        return response()->json(['message' => 'Role updated successfully']);
    }
    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json(['message' => 'Role deleted successfully']);
    }
    public function show(Role $role)
    {
        $role->load('permissions');
        $permissions = Permission::all();
        $givenPermissionIds = $role->permissions->pluck('id')->toArray();
        $availablePermissions = Permission::whereNotIn('id', $givenPermissionIds)->get();

        return view('auth.roles.show', compact('role', 'permissions', 'availablePermissions'));
        //return view('auth.users.show', compact('user', 'roles'));
    }
    public function givePermission(Request $request, Role $role)
    {
        $request->validate([
            'permission_id' => 'required|exists:roles,id',
        ]);

        $permission = Permission::findOrFail($request->permission_id);
        $role->givePermissionTo($permission);

        return redirect()->back()->with('success', 'Permission gave successfully.');
    }
    public function removePermission(Role $role, Permission $permission)
    {
        $role->revokePermissionTo($permission);

        return redirect()->back()->with('success', 'Permission revoked successfully.');
    }
}
