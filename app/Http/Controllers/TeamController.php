<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TeamInvitationRequest;
use App\Http\Requests\TeamMemberUpdateRequest;
use App\Http\Resources\TeamResource;
use App\Models\Company;
use App\Models\TeamInvitation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class TeamController extends Controller
{
    public function index(Request $request): Response
    {
        $company = $request->user()->currentCompany;
        
        // Get all users associated with the current company
        $teamMembers = $company->users()
            ->with('roles')
            ->withPivot('role')
            ->orderBy('name')
            ->get()
            ->map(function ($user) use ($company, $request) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->pivot->role,
                    'is_owner' => (bool)$user->pivot->is_owner,
                    'avatar' => $user->profile_photo_url,
                    'joined_at' => $user->pivot->created_at->format('M d, Y'),
                    'can' => [
                        'edit' => $request->user()->can('updateTeamMember', $company),
                        'delete' => $request->user()->can('removeTeamMember', $company) && !$user->pivot->is_owner && $request->user()->id !== $user->id,
                    ],
                ];
            });
        
        // Get all pending invitations
        $pendingInvitations = $company->invitations()
            ->where('expires_at', '>', Carbon::now())
            ->get()
            ->map(function ($invitation) use ($request, $company) {
                return [
                    'id' => $invitation->id,
                    'name' => $invitation->name,
                    'email' => $invitation->email,
                    'role' => $invitation->role,
                    'expires_at' => $invitation->expires_at->format('M d, Y'),
                    'created_at' => $invitation->created_at->format('M d, Y'),
                    'can' => [
                        'cancel' => $request->user()->can('inviteTeamMember', $company),
                    ],
                ];
            });

        $roles = Role::all()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'description' => $this->getRoleDescription($role->name),
                'permissions' => $role->load('permissions')->pluck('name'),
            ];
        });

        return Inertia::render('team/Index', [
            'team' => new TeamResource($teamMembers),
            'invitations' => $pendingInvitations,
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
            ],
            'roles' => $roles,
            'userPermissions' => [
                'canInviteUsers' => $request->user()->can('inviteTeamMember', $company),
                'canRemoveUsers' => $request->user()->can('removeTeamMember', $company),
                'canUpdateRoles' => $request->user()->can('updateTeamMember', $company),
            ],
        ]);
    }

    public function invite(TeamInvitationRequest $request): RedirectResponse
    {
        $company = $request->user()->currentCompany;
        
        // Check if the user already exists
        $existingUser = User::where('email', $request->email)->first();
        
        if ($existingUser) {
            // If user already belongs to the company, redirect with an error
            if ($company->users()->where('users.id', $existingUser->id)->exists()) {
                return redirect()->route('team.index')
                    ->with('error', 'This user is already a member of your team.');
            }
            
            // Create an invitation for the existing user
            $invitation = TeamInvitation::createInvitation($company, $request->validated());
            
            // Send invitation email 
            $existingUser->notify(new \App\Notifications\TeamInvitationNotification($invitation, $request->user()));
        } else {
            // Create an invitation for a new user
            $invitation = TeamInvitation::createInvitation($company, $request->validated());
            
            // Send invitation email with account setup link
            \Illuminate\Support\Facades\Notification::route('mail', $request->email)
                ->notify(new \App\Notifications\TeamInvitationNotification($invitation, $request->user()));
        }
        
        return redirect()->route('team.index')
            ->with('success', 'Invitation has been sent successfully.');
    }
    
    public function update(TeamMemberUpdateRequest $request, User $member): RedirectResponse
    {
        $company = $request->user()->currentCompany;
        
        // Update user role in the company
        $company->users()->updateExistingPivot($member->id, ['role' => $request->role]);
        
        // Update the user's Spatie role
        // First, remove any existing roles that might conflict
        $existingRole = $member->getRoleInCompany($company);
        if ($existingRole && $existingRole !== $request->role) {
            $member->removeRole($existingRole);
        }
        
        // Assign the new role
        $member->assignRole($request->role);
        
        return redirect()->route('team.index')->with('success', 'Team member role has been updated.');
    }
    
    public function destroy(Request $request, User $member): RedirectResponse
    {
        $company = $request->user()->currentCompany;
        
        // Make sure we're not removing the company owner
        $isOwner = $company->users()->where('users.id', $member->id)->wherePivot('is_owner', true)->exists();
        if ($isOwner) {
            return redirect()->route('team.index')->with('error', 'The company owner cannot be removed.');
        }
        
        // Make sure we're not removing ourselves
        if ($request->user()->id === $member->id) {
            return redirect()->route('team.index')->with('error', 'You cannot remove yourself from the team.');
        }
        
        // Remove the user from the company
        $company->users()->detach($member->id);
        
        return redirect()->route('team.index')->with('success', 'Team member has been removed.');
    }

    public function acceptInvitation(Request $request, string $token): RedirectResponse
    {
        try {
            // Find invitation by token
            $invitation = TeamInvitation::where('invitation_token', $token)
                ->where('expires_at', '>', Carbon::now())
                ->first();
            
            if (!$invitation) {
                return redirect()->route('login')
                    ->with('error', 'The invitation link has expired or is invalid.');
            }
            
            $company = $invitation->company;
            
            // Check if the user is logged in
            if ($request->user()) {
                $user = $request->user();
                
                // Check if user email matches invitation email for security
                if ($user->email !== $invitation->email) {
                    return redirect()->route('dashboard')
                        ->with('error', 'This invitation was sent to a different email address.');
                }
                
                // Check if already a member
                if ($company->users()->where('users.id', $user->id)->exists()) {
                    $invitation->delete();
                    return redirect()->route('dashboard')
                        ->with('info', "You are already a member of {$company->name}.");
                }
                
                // Add user to the company
                $user->companies()->attach($company->id, [
                    'role' => $invitation->role,
                    'joined_at' => Carbon::now(),
                ]);
                
                // Assign Spatie role
                $user->assignRole($invitation->role);
                
                // Set this as the current company for the user
                $user->update(['current_company_id' => $company->id]);
                
                // Delete the invitation
                $invitation->delete();
                
                return redirect()->route('dashboard')
                    ->with('success', "You've successfully joined {$company->name}.");
            } else {
                // User is not logged in
                // Store invitation data in session securely
                session([
                    'invitation_token' => $token,
                    'invitation_email' => $invitation->email,
                    'invitation_name' => $invitation->name,
                    'company_name' => $company->name
                ]);
                
                // Redirect to special registration route for invited users
                return redirect()->route('register.invited')
                    ->with('info', "Please create an account to join {$company->name}.");
            }
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'There was a problem with your invitation. Please contact the company administrator.');
        }
    }
    
    public function cancelInvitation(TeamInvitation $invitation): RedirectResponse
    {
        $company = request()->user()->currentCompany;
        
        // Check if the invitation belongs to the current company
        if ($invitation->company_id !== $company->id) {
            abort(403);
        }
        
        // Delete the invitation
        $invitation->delete();
        
        return redirect()->route('team.index')
            ->with('success', 'Invitation has been cancelled successfully.');
    }
    
    private function getRoleDescription(string $role): string
    {
        return match ($role) {
            'super_admin' => 'Full access to all resources and system-wide administrative settings.',
            'company_owner' => 'Full access to all resources and administrative settings within the company.',
            'manager' => 'Create and manage billboards, contracts, and view analytics.',
            'editor' => 'Create and edit billboards and limited analytics.',
            'viewer' => 'View-only access to billboards and basic reports.',
            default => 'User with custom permissions.',
        };
    }
}
