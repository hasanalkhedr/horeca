<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Storage;
use Validator;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Start the query
        $users = User::query();

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->input('search');
            $users->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role') && $request->input('role') !== '' && $request->input('role') != null) {
            dd($request);
            $role = $request->input('role');
            $users->whereHas('roles', function ($query) use ($role) {
                $query->where('name', $role);
            });
        }

        // Sort by column
        if ($request->has('sort')) {
            $sort = $request->input('sort');
            $direction = $request->input('direction', 'asc');
            $users->orderBy($sort, $direction);
        }

        // Paginate the results
        $users = $users->paginate(10); // Use paginate() instead of get()

        // Get all roles for the filter dropdown
        $roles = Role::all();

        return view('auth.users.index', compact('users', 'roles'));
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,',
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $request->except('profile_picture');
        if ($request->hasFile('profile_picture')) {
            $data['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        $user = User::create($data);
        $user->roles()->sync($request->roles);

        return response()->json(['message' => 'User created successfully']);
    }
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $data = $request->except('profile_picture');
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if it exists
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $data['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
        }
        $user->update($data);
        $user->roles()->sync($request->roles);
        return response()->json(['message' => 'User updated successfully']);
    }
    public function destroy(User $user)
    {
        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }
    public function show(User $user)
    {
        // Load the user's roles
        $user->load('roles');
        $roles = Role::all();

        return view('auth.users.show', compact('user', 'roles'));
    }
}
