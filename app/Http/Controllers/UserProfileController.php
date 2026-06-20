<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class UserProfileController extends Controller
{
    /**
     * Show the signed-in user's profile-completion page.
     */
    public function edit(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user->isOperational()) {
            return redirect()->route('operations.home');
        }

        $profile = $user->profile;

        return Inertia::render('profile/complete', [
            'profile' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar_url' => $profile?->avatar_url,
                'job_title' => $profile?->job_title,
                'company' => $profile?->company,
                'location' => $profile?->location,
                'website' => $profile?->website,
                'bio' => $profile?->bio,
            ],
            'completion' => $user->profileCompletionPercent(),
        ]);
    }

    /**
     * Save the user's profile details (and optionally their avatar).
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->isOperational()) {
            return redirect()->route('operations.home');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'job_title' => ['nullable', 'string', 'max:120'],
            'company' => ['nullable', 'string', 'max:120'],
            'location' => ['nullable', 'string', 'max:120'],
            'website' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'max:4096'],
        ]);

        $user->update(['name' => $validated['name'], 'phone' => $validated['phone'] ?? null]);

        $profile = $user->profile ?? new UserProfile(['user_id' => $user->id]);

        if ($request->hasFile('avatar')) {
            if ($profile->avatar_path !== null) {
                Storage::disk('public')->delete($profile->avatar_path);
            }

            $profile->avatar_path = $request->file('avatar')->store('avatars', 'public');
        }

        $profile->fill([
            'job_title' => $validated['job_title'] ?? null,
            'company' => $validated['company'] ?? null,
            'location' => $validated['location'] ?? null,
            'website' => $validated['website'] ?? null,
            'bio' => $validated['bio'] ?? null,
        ]);

        $user->profile()->save($profile);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Profile updated.')]);

        return to_route('planner');
    }

    /**
     * A read-only profile card for any user — shown when an operational worker
     * clicks an organizer's name on the dashboard.
     */
    public function show(User $user): JsonResponse
    {
        $profile = $user->profile;

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'account_type' => $user->account_type?->value,
                'avatar_url' => $profile?->avatar_url,
                'job_title' => $profile?->job_title,
                'company' => $profile?->company,
                'location' => $profile?->location,
                'website' => $profile?->website,
                'bio' => $profile?->bio,
                'completion' => $user->profileCompletionPercent(),
                'member_since' => $user->created_at?->toIso8601String(),
            ],
        ]);
    }
}
