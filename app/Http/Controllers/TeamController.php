<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TeamInvitationRequest;
use App\Http\Requests\TeamMemberUpdateRequest;
use App\Http\Resources\TeamResource;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Policies\CompanyTeamPolicy;
use App\Services\SubscriptionLimitService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

final class TeamController extends Controller
{
    public function __construct(
        private readonly SubscriptionLimitService $subscriptionLimitService
    ) {}

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
                    'role' => $user->pivot->role ?? $user->roles->first()->name,
                    'is_owner' => (bool) $user->pivot->is_owner,
                    'avatar' => $user->profile_photo_url,
                    'joined_at' => $user->pivot->created_at->format('M d, Y'),
                    'can' => [
                        'edit' => $request->user()->can('updateTeamMember', $company),
                        'delete' => $request->user()->can('removeTeamMember', $company) && ! $user->pivot->is_owner && $request->user()->id !== $user->id,
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

        // Filter roles for team invitations - exclude super_admin and company_owner
        // Super admins are created via tinker only, company owners are the ones inviting
        $roles = Role::whereNotIn('name', ['super_admin', 'company_owner'])
            ->get()
            ->map(function ($role) {
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

    public function update(TeamMemberUpdateRequest $request, User $member): RedirectResponse
    {
        $company = $request->user()->currentCompany;

        // Authorize using the new policy
        $this->authorize('update', [CompanyTeamPolicy::class, $company, $member]);

        // Update user role in the company
        $company->users()->updateExistingPivot($member->id, ['role' => $request->validated('role')]);

        // Update the user's Spatie role
        // First, remove any existing roles that might conflict
        $existingRole = $member->getRoleInCompany($company);
        if ($existingRole && $existingRole !== $request->validated('role')) {
            $member->removeRole($existingRole);
        }

        // Assign the new role
        $member->assignRole($request->validated('role'));

        return redirect()->route('team.index')->with('success', 'Team member role has been updated.');
    }

    public function destroy(Request $request, User $member): RedirectResponse
    {
        $company = $request->user()->currentCompany;

        // Authorize using the new policy
        $this->authorize('delete', [CompanyTeamPolicy::class, $company, $member]);

        // Remove the user from the company
        $company->users()->detach($member->id);

        return to_route('team.index')->with('success', 'Team member has been removed.');
    }

    public function acceptInvitation(Request $request, string $token): RedirectResponse
    {
        try {
            // Find invitation by token
            $invitation = TeamInvitation::where('invitation_token', $token)
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if (! $invitation) {
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
            }
            // User is not logged in
            // Store invitation data in session securely
            session([
                'invitation_token' => $token,
                'invitation_email' => $invitation->email,
                'invitation_name' => $invitation->name,
                'company_name' => $company->name,
            ]);

            // Redirect to special registration route for invited users
            return redirect()->route('register.invited')
                ->with('info', "Please create an account to join {$company->name}.");

        } catch (Exception $e) {
            return redirect()->route('login')
                ->with('error', 'There was a problem with your invitation. Please contact the company administrator.');
        }
    }

    public function invite(TeamInvitationRequest $request): RedirectResponse
    {
        $company = $request->user()->currentCompany;

        // Authorize using the new policy
        $this->authorize('invite', [CompanyTeamPolicy::class, $company]);

        // Already checked in the policy, but we still need the message
        if (!$this->subscriptionLimitService->canInviteTeamMember($company)) {
            $planId = $company->subscription_plan ?? 'free';
            $teamCount = $company->users()->count();
            $limit = \App\Services\Billing\PlanGate::limit($planId, 'team.members.max');

            $message = $planId === 'free'
                ? 'Team invitations are not available on the free plan. Upgrade to Pro or Business to invite team members.'
                : "You've reached your team member limit ({$limit}) for the {$planId} plan. Please upgrade to invite more members.";

            return redirect()->route('team.index')
                ->with('error', $message)
                ->with('upgrade_required', true);
        }

        // Get validated data
        $data = $request->validated();

        // Check if the user already exists
        $existingUser = User::where('email', $data['email'])->first();

        if ($existingUser) {
            // If user already belongs to the company, redirect with an error
            if ($company->users()->where('users.id', $existingUser->id)->exists()) {
                return redirect()->route('team.index')
                    ->with('error', 'This user is already a member of your team.');
            }

            // Create an invitation for the existing user
            $invitation = TeamInvitation::createInvitation($company, $data);

            // Send invitation email
            $existingUser->notify(new \App\Notifications\TeamInvitationNotification($invitation, $request->user()));
        } else {
            // Create an invitation for a new user
            $invitation = TeamInvitation::createInvitation($company, $data);

            // Send invitation email with account setup link
            Notification::route('mail', $data['email'])
                ->notify(new \App\Notifications\TeamInvitationNotification($invitation, $request->user()));
        }

        return to_route('team.index')
            ->with('success', 'Invitation has been sent successfully.');
    }

    public function cancelInvitation(TeamInvitation $invitation): RedirectResponse
    {
        $company = request()->user()->currentCompany;

        // Check if the invitation belongs to the current company
        if ($invitation->company_id !== $company->id) {
            abort(403);
        }

        // Authorize using the new policy
        $this->authorize('manageInvitations', [CompanyTeamPolicy::class, $company]);

        // Delete the invitation
        $invitation->delete();

        return redirect()->route('team.index')
            ->with('success', 'Invitation has been cancelled successfully.');
    }

    public function inviteModal(Request $request): Response|RedirectResponse
    {
        $company = $request->user()->currentCompany;

        // Consolidated authorization check with detailed error messages
        $authResult = $this->getDetailedInviteAuthorization($request->user(), $company);

        if (!$authResult['authorized']) {
            $redirect = to_route('team.index')->with('error', $authResult['message']);

            if (isset($authResult['upgrade_required']) && $authResult['upgrade_required']) {
                $redirect->with('upgrade_required', true);
            }

            return $redirect;
        }

        // Filter roles for team invitations - exclude super_admin and company_owner
        $roles = Role::whereNotIn('name', ['super_admin', 'company_owner'])
            ->get()
            ->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'description' => $this->getRoleDescription($role->name),
                    'permissions' => $role->load('permissions')->pluck('name'),
                ];
            });

        return Inertia::render('team/InviteUserModal', [
            'roles' => $roles,
        ]);
    }

    public function editModal(Request $request, User $member): Response
    {
        $company = $request->user()->currentCompany;

        // Get the member with pivot data
        $memberWithPivot = $company->users()->where('users.id', $member->id)->first();

        // Check if the member belongs to the current company
        if (! $memberWithPivot) {
            abort(404);
        }

        // Filter roles for team invitations - exclude super_admin and company_owner
        // Super admins are created via tinker only, company owners are the ones inviting
        $roles = Role::whereNotIn('name', ['super_admin', 'company_owner'])
            ->get()
            ->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'description' => $this->getRoleDescription($role->name),
                    'permissions' => $role->load('permissions')->pluck('name'),
                ];
            });

        $memberData = [
            'id' => $memberWithPivot->id,
            'name' => $memberWithPivot->name,
            'email' => $memberWithPivot->email,
            'role' => $memberWithPivot->pivot->role,
            'is_owner' => (bool) $memberWithPivot->pivot->is_owner,
            'avatar' => $memberWithPivot->profile_photo_url,
            'joined_at' => $memberWithPivot->pivot->created_at->format('M d, Y'),
            'can' => [
                'edit' => $request->user()->can('updateTeamMember', $company),
                'delete' => $request->user()->can('removeTeamMember', $company) && ! $memberWithPivot->pivot->is_owner && $request->user()->id !== $memberWithPivot->id,
            ],
        ];

        return Inertia::render('team/EditMemberModal', [
            'member' => $memberData,
            'roles' => $roles,
        ]);
    }

    /**
     * Get detailed authorization result for team member invitation
     */
    private function getDetailedInviteAuthorization(User $user, $company): array
    {
        // Check company access
        if (!$user->canAccessCompany($company)) {
            return [
                'authorized' => false,
                'reason' => 'company_access',
                'message' => 'You do not have access to this company.',
            ];
        }

        // Check role permission
        $hasRolePermission = $user->isOwnerOf($company) ||
                            in_array($user->getRoleInCompany($company), ['company_owner', 'manager']);

        if (!$hasRolePermission) {
            return [
                'authorized' => false,
                'reason' => 'role_permission',
                'message' => 'You are not authorized to invite team members. Only company owners and managers can invite team members.',
            ];
        }

        // Check subscription plan and limits
        $planId = $company->subscription_plan ?? 'free';
        $planAllows = \App\Services\Billing\PlanGate::allows($planId, 'team.invitations', false);

        if (!$planAllows) {
            $teamCount = $company->users()->count();
            $limit = \App\Services\Billing\PlanGate::limit($planId, 'team.members.max');

            $message = $planId === 'free'
                ? 'Team invitations are not available on the free plan. Upgrade to Pro or Business to invite team members.'
                : (is_null($limit)
                    ? 'Your current plan does not allow additional team invitations.'
                    : "You've reached your team member limit ({$limit}) for the {$planId} plan. Please upgrade to invite more members.");

            return [
                'authorized' => false,
                'reason' => 'subscription_limit',
                'message' => $message,
                'upgrade_required' => true,
                'current_plan' => $planId,
                'team_count' => $teamCount,
                'team_limit' => $limit,
            ];
        }

        // Check if at team limit (additional check via SubscriptionLimitService)
        if (!$this->subscriptionLimitService->canInviteTeamMember($company)) {
            $teamCount = $company->users()->count();
            $limit = \App\Services\Billing\PlanGate::limit($planId, 'team.members.max');

            $message = is_null($limit)
                ? "Your current plan ({$planId}) does not allow additional team invitations."
                : "You've reached your team member limit ({$limit}) for the {$planId} plan. Please upgrade to invite more members.";

            return [
                'authorized' => false,
                'reason' => 'team_limit_reached',
                'message' => $message,
                'upgrade_required' => true,
                'current_plan' => $planId,
                'team_count' => $teamCount,
                'team_limit' => $limit,
            ];
        }

        return [
            'authorized' => true,
            'reason' => null,
            'message' => null,
        ];
    }

    public function show(Request $request, User $member): Response
    {
        $company = $request->user()->currentCompany;

        // Authorize using the CompanyTeamPolicy
        $this->authorize('view', [CompanyTeamPolicy::class, $company, $member]);

        // Get the member with pivot data and load relationships
        $memberWithPivot = $company->users()
            ->with(['roles.permissions'])
            ->where('users.id', $member->id)
            ->first();

        // Get member's role - keeping the complex logic commented for future implementation
        /*$roleName = match (true) {
            // If we have a member with pivot role, use that
            $memberWithPivot && !empty($memberWithPivot->pivot->role) => $memberWithPivot->pivot->role,
            // If member exists and is owner but no pivot role, use company_owner
            $memberWithPivot && $memberWithPivot->pivot->is_owner => 'company_owner',
            // If member exists with Spatie roles, use first role
            $memberWithPivot && $memberWithPivot->roles->isNotEmpty() => $memberWithPivot->roles->first()->name,
            // If none of the above, we have no valid member/role
            default => null
        };*/

        // For now, use a simpler approach that works with seeded data
        if (!$memberWithPivot) {
            abort(404);
        }

        // Get role name from Spatie first, then fallback to pivot if it exists
        $roleName = $memberWithPivot->roles->first()?->name ?? $memberWithPivot->pivot->role;

        // Get full role details with permissions
        $role = Role::where('name', $roleName)
            ->with('permissions')
            ->first();

        // Map the team member data for the view
        $memberData = [
            'id' => $memberWithPivot->id,
            'name' => $memberWithPivot->name,
            'email' => $memberWithPivot->email,
            'role' => [
                'name' => $memberWithPivot->pivot->role,
                'description' => $this->getRoleDescription($memberWithPivot->pivot->role),
                'permissions' => $role ? $role->permissions->pluck('name') : [],
            ],
            'is_owner' => (bool) $memberWithPivot->pivot->is_owner,
            'avatar' => $memberWithPivot->profile_photo_url,
            'joined_at' => $memberWithPivot->pivot->created_at->format('M d, Y'),
            'can' => [
                'edit' => $request->user()->can('update', [CompanyTeamPolicy::class, $company, $memberWithPivot]),
                'delete' => $request->user()->can('delete', [CompanyTeamPolicy::class, $company, $memberWithPivot]),
            ],
        ];

        return Inertia::render('team/Show', [
            'member' => $memberData,
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
            ],
        ]);
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
