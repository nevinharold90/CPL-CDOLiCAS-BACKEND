<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $firstName  = $this->userCredential?->first_name ?? $this->first_name;
        $lastName   = $this->userCredential?->last_name ?? $this->last_name;
        $middleName = $this->userCredential?->middle_name ?? $this->middle_name;

        // Force title capitalization on first_name (e.g., "nevin harold" -> "Nevin Harold")
        $formattedFirstName = Str::title($firstName ?? $this->username);

        return [
            'id'             => $this->id,
            'employee_id_no' => $this->employee_id_no,
            'username'       => $this->username,

            // Capitalized fields
            'first_name'     => $formattedFirstName,
            'last_name'      => Str::title($lastName),
            'middle_name'    => Str::title($middleName),
            'display_name'   => $formattedFirstName,
            'full_name'      => Str::title(trim("{$firstName} {$middleName} {$lastName}")),

            'email'          => $this->email,
            'status'         => $this->status,
            'role'           => $this->role,
        ];
    }
}
