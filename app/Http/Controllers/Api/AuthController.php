<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{

    /**
     * Process a registration request
     *
     * @param Request $request
     * @return Response
     */
    public function register(Request $request)
    {
        $rules = [
            'name' => 'required|min:5:max:15',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|max:20'
        ];

        $data = $request->validate($rules);

        User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password'))
        ]);

        return response()->json([
            'result' => 'OK'
        ], 201);
    }

    /**
     * Process a login request
     *
     * @param Request $request
     * @return Response
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required'
        ]);

        $user = User::where('email', '=', $request->get('email'))->get()->first();
        if (!$user) {
            return response()->json([
                'result' => 'ERROR',
                'message' => 'Invalid credentials provided.'
            ], 422);
        }

        if (!Hash::check($request->get('password'), $user->password)) {
            return response()->json([
                'result' => 'ERROR',
                'message' => 'Invalid credentials provided.'
            ], 400);
        }

        return response()->json([
            'result' => 'OK',
            'bearer' => $user->createToken($request->get('device_name'))->plainTextToken,
            'name' => $user->name
        ]);
    }
}
