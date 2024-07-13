<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->input('page_size', 10); // Default page size is 10
        $pageNumber = $request->input('page', 1); // Default page number is 1

        $filters = $request->query();

        $users = User::query();

        if (isset($filters['first_name'])) {
            $firstName = $filters['first_name'];
            $users->where('first_name', 'like', '%'. $firstName .'%');
        }
        if (isset($filters['last_name'])) {
            $lastName = $filters['last_name'];
            $users->where('last_name', 'like', '%'. $lastName .'%');
        }
        if (isset($filters['user_email'])) {
            $email = $filters['user_email'];
            $users->where('user_email', 'like', '%'. $email .'%');
        }

        return response()->json($users->paginate($pageSize, ['*'], 'users', $pageNumber));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check if the password is present in the request
        if (!$request->has('password')) {
            $request->merge(['password' => '12345678']); // Assign a default password
        }

        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'user_email' => 'required|email|unique:users,user_email',
            'password' => 'required|string|min:8',
        ]);

        $validatedData['password'] = Hash::make($validatedData['password']); // Hash the password

        $user = User::create($validatedData);
        return response()->json(['message' => 'User created successfully', 'data' => $user], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return response()->json(['data' => $user]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'user_email' => 'nullable|email|unique:users,user_email,' . $user->id,
            'password' => 'nullable|string|min:8',
        ]);

        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']); // Hash the new password
        }

        $user->update($validatedData);
        return response()->json(['message' => 'User updated successfully', 'data' => $user]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }
}
