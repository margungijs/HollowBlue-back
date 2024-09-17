<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    public function loginOrRegister(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
        ]);

        // Attempt to find the user by email
        $user = User::where('email', $request->input('email'))->first();

        if ($user) {
            Auth::login($user);

            $expirationTimeInMinutes = 60;
            $token = $user->createToken('authToken', ['*'], null, $expirationTimeInMinutes)->accessToken;

            return response()->json(['message' => 'User logged in successfully', 'token' => $token->token]);
        } else {
            $password = $this->generateRandomPassword();

            // User not found, register a new user
            $newUser = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($password),
                // Add any other necessary fields
            ]);

            // Log in the newly registered user
            Auth::login($newUser);

            $expirationTimeInMinutes = 60;
            $token = $newUser->createToken('authToken', ['*'], null, $expirationTimeInMinutes)->accessToken;

            // You may want to return a response or redirect here
            return response()->json(['message' => 'User registered and logged in successfully', 'token' => $token->token]);
        }
    }

    private function generateRandomPassword($length = 16)
    {
        // Generate a random password using Laravel's str_random function
        return Str::random($length);
    }
}

