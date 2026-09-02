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
        // 1. Validate payload against actual schema rules
        $validated = $request->validate([
            'first_name'          => 'required|string',
            'last_name'           => 'required|string',
            'middle_name'         => 'nullable|string',
            'address'             => 'nullable|string',
            'c_number'            => 'required|string',
            'username'            => 'required|string|unique:users,username',
            'email'               => 'nullable|email|unique:users,email',
            'password'            => 'required|string|min:6',
            'sex'                 => 'required|in:male,female',
            'employee_id_no'      => 'nullable|string|unique:users,employee_id_no',
            'organization_office' => 'nullable|string',
            'office_address'      => 'nullable|string',
        ]);

        // 2. Persist across both tables via Database Transaction
        $clientUser = DB::transaction(function () use ($validated) {

            // Step A: Store visitor profile identity record
            $credential = UserCredential::create([
                'first_name'          => $validated['first_name'],
                'last_name'           => $validated['last_name'],
                'middle_name'         => $validated['middle_name'] ?? null,
                'address'             => $validated['address'] ?? null,
                'c_number'            => $validated['c_number'],
                'organization_office' => $validated['organization_office'] ?? null,
                'office_address'      => $validated['office_address'] ?? null,
                'has_account'         => true, // Account is being created now
            ]);

            // Step B: Create primary system login tied to visitor profile ID
            return User::create([
                'user_credential_id' => $credential->id,
                'username'           => $validated['username'],
                'password'           => Hash::make($validated['password']),
                'role'               => 'client', // Assigning the role for client users
                'c_number'           => $validated['c_number'],
                'email'              => $validated['email'] ?? null,
                'sex'                => $validated['sex'],
                'employee_id_no'     => $validated['employee_id_no'] ?? null,
                'status'             => 'offline',
            ]);
        });

        // 3. Load visitor profile relation for output mapping
        $clientUser->load('userCredential');

        return response()->json([
            'status'  => 'success',
            'message' => 'Client account provisioned successfully.',
            'data'    => [
                'id'         => $clientUser->id,
                'username'   => $clientUser->username,
                'email'      => $clientUser->email,
                'first_name' => $clientUser->userCredential?->first_name,
                'last_name'  => $clientUser->userCredential?->last_name,
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
                    'home_address'             => $request->home_address ?? null,
                    'role'                  => 'admin', // Or whatever flag matches your system for system management
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

        public function indexClient()
        {
            // Fetch users where the role is either 'Admin' OR 'Dev'
            $adminsAndDevs = User::with('userCredential')
                ->whereIn('role', ['client'])
                ->paginate(10); // 10 users per page

            return response()->json([
                'status' => 'success',
                'data'   => UserResource::collection($adminsAndDevs)
            ]);
        }
}
