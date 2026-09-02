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
            // Wrap the authenticated user in UserResource
            return response()->json([
                'authenticated' => true,
                'user' => new UserResource($request->user()),
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

            // 1. Update online status & pulse cache
            $user->status = 'online';
            $user->save();
            $user->refresh();

            \Illuminate\Support\Facades\Cache::put('user-is-online-' . $user->id, true, now()->addMinutes(5));

            // 2. Generate Sanctum access token (single issuance)
            $token = $user->createToken('MyApp')->plainTextToken;

            // 3. Optional: Email notification logic
            // if ($user->role === 'admin' || $user->role === 'superadmin') {
            //     \Illuminate\Support\Facades\Mail::to('cabarrubias.nevinharold@gmail.com')
            //         ->send(new \App\Mail\AdminLoginNotification($user));
            // }

            return response()->json([
                'message' => 'Login successful',
                'token' => $token,
                'user' => new UserResource($user)
            ], 200);
        }

}
