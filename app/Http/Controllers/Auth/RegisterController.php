<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserCredential;

class RegisterController extends BaseController
{

    public function clientRegister(Request $request)
    {
                // 1. Validate incoming client registration data
            // $request->validate([
            //     'name'     => 'required|string|max:255',
            //     'username' => 'required|string|unique:users,username|max:50',
            //     'email'    => 'required|email|unique:users,email|max:255',
            //     'password' => 'required|string|min:8', // Client passwords should be secure!
            // ]);

            // 2. Wrap creation in a transaction to guarantee data safety across both tables
            $clientUser = DB::transaction(function () use ($request) {

                // Step A: Create the Client's base profile identity record
                $credential = UserCredential::create([
                    'last_name'             => $request->last_name,
                    'first_name'            => $request->first_name,
                    'middle_name'           => $request->middle_name ?? null,
                    'address'               => $request->address ?? null,
                    'role'                  => 'Client', // Or whatever flag matches your system for system management
                    'organization_office'   => $request->organization_office ?? null,
                    'c_number'              => $request->c_number ?? null,
                    'office_address'        => $request->office_address ?? null,
                ]);

                // Step B: Create the Client's system login account linked to that profile ID
                return User::create([
                    'user_credential_id'    => $credential->id,
                    'employee_id_no'        => $request->employee_id_no,
                    'username'              => $request->username,
                    'email'                 => $request->email,
                    'password'              => Hash::make($request->password),
                    'role'                  => $request->role, // Assigning the highest role from your schema (Guest|Client|Staff|Dev)
                    'status'                => 'offline',
                    'sex'                   => $request->sex,
                    'has_account'           => true
                ]);
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'Client account provisioned successfully.',
                'data'    => [
                    'id'                    => $clientUser->id,
                    'username'              => $clientUser->username,
                    'email'                 => $clientUser->email,
                    'role'                  => $clientUser->role,
                    'first_name'            => $clientUser->userCredential?->first_name,
                    'last_name'             => $clientUser->userCredential?->last_name,
                ]
            ], 201);
    }

    // ADMIN ACCOUNT
    public function createAdmin(Request $request)
        {
            // 1. Validate incoming admin registration data
            // $request->validate([
            //     'name'     => 'required|string|max:255',
            //     'username' => 'required|string|unique:users,username|max:50',
            //     'email'    => 'required|email|unique:users,email|max:255',
            //     'password' => 'required|string|min:8', // Admin passwords should be secure!
            // ]);

            // 2. Wrap creation in a transaction to guarantee data safety across both tables
            $adminUser = DB::transaction(function () use ($request) {

                // Step A: Create the Admin's base profile identity record
                $credential = UserCredential::create([
                    'last_name'             => $request->last_name,
                    'first_name'            => $request->first_name,
                    'middle_name'           => $request->middle_name ?? null,
                    'address'               => $request->address ?? null,
                    'role'                  => 'Admin', // Or whatever flag matches your system for system management
                    'organization_office'   => $request->organization_office ?? null,
                    'c_number'              => $request->c_number ?? null,
                    'office_address'        => $request->office_address ?? null,
                ]);

                // Step B: Create the Admin's system login account linked to that profile ID
                return User::create([
                    'user_credential_id'    => $credential->id,
                    'employee_id_no'        => $request->employee_id_no,
                    'username'              => $request->username,
                    'email'                 => $request->email,
                    'password'              => Hash::make($request->password),
                    'role'                  => $request->role, // Assigning the highest role from your schema (Guest|Client|Staff|Dev)
                    'status'                => 'offline',
                    'sex'                   => $request->sex,
                    'has_account'           => true
                ]);
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'System Administrator account provisioned successfully.',
                'data'    => [
                    'id'                    => $adminUser->id,
                    'username'              => $adminUser->username,
                    'email'                 => $adminUser->email,
                    'role'                  => $adminUser->role,
                    'first_name'            => $adminUser->userCredential?->first_name,
                    'last_name'             => $adminUser->userCredential?->last_name,
                ]
            ], 201);
        }

        public function indexAdmin()
        {
            // Fetch users where the role is either 'Admin' OR 'Dev'
            $adminsAndDevs = User::with('userCredential')
                ->whereIn('role', ['admin', 'superadmin'])
                ->paginate(10); // 10 users per page

            return response()->json([
                'status' => 'success',
                'data'   => UserResource::collection($adminsAndDevs)
            ]);
        }
}
