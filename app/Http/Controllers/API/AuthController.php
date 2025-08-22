<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {  //body of requst
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'phone' => 'required|string|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,cashier,customer'
        ]);

        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        //jason response
        return response()->json($user, 201);
    }

    public function login(Request $request)
    {  //body of requst
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        //   //jason response return user + its id
        return response()->json(['user' => $user, 'user_id' => $user->id]);
    }

    public function logout(Request $request)
    {
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function profile(Request $request)
    {  // return user obj
        return response()->json($request->user);
    }

    public function updateProfile(Request $request)
    {  //body of requst
        $user = $request->user;
        $data = $request->validate([
            'name' => 'sometimes|string',
            'phone' => 'sometimes|string|unique:users,phone,' . $user->id,
        ]);
        $user->update($data);
        return response()->json($user);
    }

    public function resetPassword(Request $request)
{
    // validate input
    $data = $request->validate([
        'user_id' => 'required|exists:users,id',
        'password' => 'required|string|min:6|confirmed'
    ]);

    // get user
    $user = User::find($data['user_id']);

    // update password
    $user->password = Hash::make($data['password']);
    $user->save();

    return response()->json(['message' => 'Password updated successfully']);
}

}
