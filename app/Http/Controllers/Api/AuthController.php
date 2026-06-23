<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|string|min:8|confirmed',
            'phone'      => 'nullable|string|max:20',
            'gender'     => 'nullable|in:male,female,other,prefer_not_to_say',
        ]);

        $name = trim($validated['first_name'] . ' ' . $validated['last_name']);

        $user = User::create([
            'name'     => $name,
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status'   => 'active',
        ]);

        $user = $user->fresh();

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'first_name' => $validated['first_name'],
                'last_name'  => $validated['last_name'],
                'gender'     => $validated['gender'] ?? null,
                'phone'      => $validated['phone'] ?? null,
            ]
        );

        $user = $user->fresh();
        
        $user->updateQuietly(['name' => $name]);

        $token = $user->createToken('pwa-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->formatUser($user),
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['Your account is not active.'],
            ]);
        }

        $token = $user->createToken('pwa-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->formatUser($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    public function user(Request $request)
    {
        return response()->json($this->formatUser($request->user()));
    }

    public function profile(Request $request)
    {
        $user = $request->user()->load('profile');

        return response()->json([
            'name'              => $user->name,
            'email'             => $user->email,
            'first_name'        => $user->profile?->first_name,
            'last_name'         => $user->profile?->last_name,
            'phone'             => $user->profile?->phone,
            'gender'            => $user->profile?->gender,
            'dob'               => $user->profile?->dob,
            'bio'               => $user->profile?->bio,
            'city'              => $user->profile?->city,
            'address'           => $user->profile?->address,
            'is_phone_private'  => $user->profile?->is_phone_private ?? true,
            'is_dob_private'    => $user->profile?->is_dob_private ?? true,
            'is_address_private'=> $user->profile?->is_address_private ?? true,
            'avatar_url'        => $user->getFilamentAvatarUrl(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'first_name'         => 'sometimes|string|max:255',
            'last_name'          => 'sometimes|string|max:255',
            'phone'              => 'sometimes|nullable|string|max:20',
            'gender'             => 'sometimes|nullable|in:male,female,other,prefer_not_to_say',
            'dob'                => 'sometimes|nullable|date',
            'bio'                => 'sometimes|nullable|string|max:1000',
            'city'               => 'sometimes|nullable|string|max:255',
            'address'            => 'sometimes|nullable|string|max:1000',
            'is_phone_private'   => 'sometimes|boolean',
            'is_dob_private'     => 'sometimes|boolean',
            'is_address_private' => 'sometimes|boolean',
        ]);

        $user = $request->user();

        if (isset($validated['first_name']) || isset($validated['last_name'])) {
            $name = trim(
                ($validated['first_name'] ?? $user->profile?->first_name) . ' ' .
                ($validated['last_name'] ?? $user->profile?->last_name)
            );
            $user->updateQuietly(['name' => $name]);
        }

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        $user->refresh();

        return response()->json($this->formatUser($user));
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = $request->user();
        $profile = $user->profile()->firstOrCreate(['user_id' => $user->id]);

        $profile->clearMediaCollection('avatar');
        $profile->addMediaFromRequest('avatar')->toMediaCollection('avatar');

        return response()->json([
            'avatar_url' => $user->getFilamentAvatarUrl(),
        ]);
    }

    

    private function formatUser(User $user): array
    {
        return [
            'id' => $user->user_key_id, // public-facing token, not internal id
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ];
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Password updated!']);
    }

    
}
