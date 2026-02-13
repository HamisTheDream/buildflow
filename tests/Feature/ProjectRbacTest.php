<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProjectRbacTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $admin;
    private User $member;
    private User $viewer;
    private Organization $org;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Org
        $this->owner = User::factory()->create();

        $plan = \App\Models\Plan::create([
            'name' => 'Free Plan',
            'slug' => 'free',
            'key' => 'free',
            'stripe_id' => 'price_fake',
            'price' => 0,
            'is_active' => true,
        ]);

        $this->org = Organization::create([
            'name' => 'Test Org',
            'type' => 'company',
            'plan_id' => $plan->id,
            'subscription_status' => 'active',
            'paid_until' => now()->addYear(),
        ]);

        // Attach owner
        $this->org->users()->attach($this->owner->id, ['role' => 'owner']);
        $this->owner->update(['current_organization_id' => $this->org->id]);

        // 2. Setup Project
        $this->project = Project::create([
            'organization_id' => $this->org->id,
            'name' => 'Test Project',
            'status' => 'active',
        ]);

        // Owner is project owner
        $this->project->members()->attach($this->owner->id, ['role' => 'owner']);


        // 3. Create other users
        $this->admin = User::factory()->create(['current_organization_id' => $this->org->id]);
        $this->org->users()->attach($this->admin->id, ['role' => 'admin']);

        $this->member = User::factory()->create(['current_organization_id' => $this->org->id]);
        $this->org->users()->attach($this->member->id, ['role' => 'member']);

        $this->viewer = User::factory()->create(['current_organization_id' => $this->org->id]);
        $this->org->users()->attach($this->viewer->id, ['role' => 'member']); // org member, but project viewer

        // 4. Assign Project Roles
        // Admin -> PM
        $this->project->members()->attach($this->admin->id, ['role' => 'pm']);

        // Member -> Clerk
        $this->project->members()->attach($this->member->id, ['role' => 'clerk']);

        // Viewer -> Viewer
        $this->project->members()->attach($this->viewer->id, ['role' => 'viewer']);

        // Disable subscription middleware for RBAC tests
        $this->withoutMiddleware([
            \App\Http\Middleware\EnsureOrgActiveAccess::class,
            \App\Http\Middleware\EnsureOrgHasAccess::class
        ]);
    }

    public function test_viewer_cannot_create_task()
    {
        $this->actingAs($this->viewer)
            ->post(route('projects.tasks.store', $this->project), [
                'title' => 'Illegal Task',
                'status' => 'todo',
                'priority' => 'normal',
            ])
            ->assertForbidden(); // Should be 403
    }

    public function test_clerk_can_create_task()
    {
        $this->actingAs($this->member) // clerk
            ->post(route('projects.tasks.store', $this->project), [
                'title' => 'Legal Task',
                'status' => 'todo',
                'priority' => 'normal',
            ])
            ->assertRedirect(); // Success redirect

        $this->assertDatabaseHas('project_tasks', ['title' => 'Legal Task']);
    }

    public function test_viewer_cannot_update_role()
    {
        // Try to promote self
        $this->actingAs($this->viewer)
            ->patch(route('projects.team.update', [$this->project, $this->viewer]), [
                'role' => 'pm',
            ])
            ->assertForbidden();

        // Try to demote owner
        $this->actingAs($this->viewer)
            ->patch(route('projects.team.update', [$this->project, $this->owner]), [
                'role' => 'viewer',
            ])
            ->assertForbidden();
    }

    public function test_pm_cannot_change_project_owner_role()
    {
        // PM tries to demote Project Owner
        $this->actingAs($this->admin)
            ->patch(route('projects.team.update', [$this->project, $this->owner]), [
                'role' => 'viewer',
            ])
            ->assertForbidden();
    }

    public function test_nobody_can_change_org_owner_project_role()
    {
        // Even if I am project owner (which owner is, but let's say another admin was project owner)
        // Org owner must be protected.
        // In this setup, Org Owner IS Project Owner, so test above covers it.

        // Let's create a scenario where Org Owner is just a 'viewer' in a project managed by someone else.
        // (Unlikely flows but possible).

        // Actually, the policy says: if target->isOrgOwner(), return false.
        // Let's verify that logic directly.

        $this->assertTrue($this->owner->isOrgOwner($this->org->id));

        // Try to change owner's role as Admin (who is PM) -> Forbidden
        $this->actingAs($this->admin)
            ->patch(route('projects.team.update', [$this->project, $this->owner]), ['role' => 'viewer'])
            ->assertForbidden();
    }

    public function test_cannot_set_role_to_owner()
    {
        // Owner tries to promote member to 'owner' via simple role change -> Should fail (must use transfer)
        $this->actingAs($this->owner)
            ->patch(route('projects.team.update', [$this->project, $this->member]), [
                'role' => 'owner'
            ])
            ->assertSessionHas('error', 'Use the Transfer Ownership flow to change the project owner.');
    }

    public function test_cannot_change_self_role()
    {
        // PM tries to change self to viewer
        $this->actingAs($this->admin)
            ->patch(route('projects.team.update', [$this->project, $this->admin]), [
                'role' => 'viewer'
            ])
            ->assertForbidden();
    }

    public function test_org_member_cannot_promote_self_in_org()
    {
        // Route for org member update: org.members.update

        $this->actingAs($this->member)
            ->patch(route('org.members.update', $this->member), ['role' => 'admin'])
            ->assertForbidden();
    }

    public function test_org_admin_cannot_demote_org_owner()
    {
        $this->actingAs($this->admin)
            ->patch(route('org.members.update', $this->owner), ['role' => 'member'])
            ->assertForbidden();
    }
}
