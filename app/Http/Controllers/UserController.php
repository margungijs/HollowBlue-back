<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\UserImage;
use Illuminate\Support\Facades\Validator;

function createProfileImage($user, $image)
{
    $imageName = '' . request()->name . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

    $image->storeAs('public/images', $imageName);

    return UserImage::create([
        'user' => $user,
        'image' => $imageName,
    ]);
}

class UserController extends Controller
{
    public static function store(Request $request){
        $validation = Validator::make($request->all(), [
            'name' => 'required|unique:users,name|between:3,30',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'passwordc' => 'required|same:password'
        ]);

        if($validation->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'The payload is not formatted correctly',
                'errors' => $validation->errors()
            ], 201);
        }

        $data = $validation->validated();

        User::create($data);

        return response()->json([
            'status' => 201,
            'message' => 'User successfully created.'
        ], 201);
    }

    public static function login(Request $request){
        $validation = Validator::make($request->all(), [
            'name' => 'required',
            'password' => 'required',
        ]);

        if($validation->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'The payload is not formatted correctly',
                'errors' => $validation->errors()
            ], 201);
        }

        $credentials = $request->only('name', 'password');

        $user = User::where('name', isset($credentials['name']) ? $credentials['name'] : '')->first();

        if (!$user) {
            return response()->json([
                'error' => 'Username not found',
                'status' => 401,
            ], 201);
        }

        if (Auth::attempt($credentials, $request->has('remember'))) {
            $user = Auth::user();

            $expirationTimeInMinutes = 60;
            $token = $user->createToken('authToken', ['*'], null, 3600)->accessToken;
            $token->expires_at = now()->addMinutes($expirationTimeInMinutes);
            $token->save();

            return response()->json([
                'token' => $token->token,
                'expire' => $token->expires_at,
                'id' => $user->id,
                'status' => 200
            ]);
        }

        return response()->json([
            'password' => 'Password incorrect',
            'status' => 401
        ], 401);
    }

    public static function users() {
        return response()->json([
            'users' => User::all()
        ]);
    }

    public static function delete($id){
        $user = User::find($id);

        if ($user) {
            $user->delete(); // Delete the user
            return response()->json(['message' => 'User deleted successfully']);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }
    }

    public static function user($user) {
        $user = User::with(['image', 'scores'])->find($user);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json([
            'user' => $user,
            'status' => 200,
        ]);
    }

    public static function image(Request $request, $user){
        $validation = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:10000',
        ]);

        if($validation->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'The payload is not formatted correctly',
                'errors' => $validation->errors()
            ], 422);
        }

        $userImage = UserImage::where('user', $user)->first();

        if ($userImage) {
            $previousImage = $userImage->image;

            if ($previousImage) {
                $imagePath = storage_path('app/public/images/' . $previousImage);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
        }

        createProfileImage($user, $request->image);

        return response()->json([
            'status' => 200,
            'events' => 'Images added'
        ]);
    }

}
