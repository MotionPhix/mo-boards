<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TeamInvitation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class InvitedRegisterController extends Controller
{
    /**
     * Display the invited registration view.
     */
    public function create(): Response
    {
        if (!session('invitation_token')) {
            return redirect()->route('login');
        }

        return Inertia::render('auth/InvitedRegister', [
            'email' => session('invitation_email'),
            'name' => session('invitation_name'),
            'companyName' => session('company_name'),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $token = session('invitation_token');

        if (!$token) {
            return redirect()->route('login')
                ->with('error', 'Your invitation session has expired. Please click the invitation link again.');
        }

        // Find the invitation
        $invitation = TeamInvitation::where('invitation_token', $token)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$invitation) {
            return redirect()->route('login')
                ->with('error', 'The invitation link has expired or is invalid.');
        }

        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Ensure the email matches the invitation
        if ($request->email !== $invitation->email) {
            return redirect()->back()
                ->withErrors(['email' => 'The email address doesn\'t match the invitation.']);
        }

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $invitation->email,
            'password' => Hash::make($request->password),
        ]);

        // Add user to the company
        $company = $invitation->company;
        $user->companies()->attach($company->id, [
            'role' => $invitation->role,
            'joined_at' => Carbon::now(),
        ]);

        // Set as current company
        $user->current_company_id = $company->id;
        $user->save();

        // Assign role
        $user->assignRole($invitation->role);

        // Delete the invitation
        $invitation->delete();

        // Clear session data
        session()->forget(['invitation_token', 'invitation_email', 'invitation_name', 'company_name']);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', "Welcome to {$company->name}! Your account has been created successfully.");
    }
}
