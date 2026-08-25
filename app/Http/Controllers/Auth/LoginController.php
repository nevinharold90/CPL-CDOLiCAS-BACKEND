<?php

namespace App\Http\Controllers\Auth;

// use App\Models\Users;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
// use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\UserResource;


class LoginController extends BaseController
{

    /**
         * Return current authenticated user data.
         */
        public function me(Request $request)
        {
            return response()->json([
                'authenticated' => true,
                'user' => $request->user(),
            ]);
        }
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string|min:5',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $credentials = $request->only('username', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        /** @var \App\Models\User $user **/
        $user = Auth::user();

        // Email Logic
        if ($user->role === 'admin' || $user->role === 'superadmin') {
            \Illuminate\Support\Facades\Mail::to('cabarrubias.nevinharold@gmail.com')
                ->send(new \App\Mail\AdminLoginNotification($user));
        }

        /** @var \App\Models\User $user **/
        $user = Auth::user();

        // Create access token
        $token = $user->createToken('MyApp')->plainTextToken;

        $user->status = 'online';
        $user->save();
        $user->refresh();

        // Record their "Pulse" in the Cache as well
        \Illuminate\Support\Facades\Cache::put('user-is-online-' . $user->id, true, now()->addMinutes(5));

        // Create access token
        $token = $user->createToken('MyApp')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => new UserResource($user)
        ], 200);
    }

}
