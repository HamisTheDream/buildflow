<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Admin;
use App\Models\Announcement;
use App\Models\Attachment;
use App\Models\CRM\Deal;
use App\Models\CRM\Lead;
use App\Models\CRM\Property;
use App\Models\CRM\PropertyUnit;
use App\Models\Finance\Budget;
use App\Models\Finance\Expense;
use App\Models\Finance\Invoice;
use App\Models\Finance\InvoicePayment;
use App\Models\HR\Department;
use App\Models\HR\Employee;
use App\Models\HR\Leave;
use App\Models\HR\Payroll;
use App\Models\Organization;
use App\Models\OrganizationInvite;
use App\Models\OrganizationNote;
use App\Models\OrganizationTask;
use App\Models\OwnerDeal;
use App\Models\Payment;
use App\Models\PaymentNote;
use App\Models\Plan;
use App\Models\Project;
use App\Models\ProjectCost;
use App\Models\ProjectIssue;
use App\Models\ProjectLog;
use App\Models\ProjectMedia;
use App\Models\ProjectReport;
use App\Models\ProjectTask;
use App\Models\ProjectUnit;
use App\Models\SupportTicket;
use App\Models\SupportTicketNote;
use App\Models\TodayLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Super Admin organization soft-delete: full cascade, restore, and
 * authorization. Covers the 2026-09-25 requirement that deleting an
 * organization soft-deletes its entire database (without touching R2
 * objects) and that restore brings the cascade back.
 *
 * NOTE: requires the `2026_09_25_130000_add_soft_deletes_to_org_scoped_tables`
 * migration to have run.
 */
class OrganizationSoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $superAdmin;
    protected Admin $regularAdmin;
    protected User $user;
    protected Organization $organization;
    protected Plan $freePlan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->freePlan = Plan::create([
            'key' => 'free',
            'name' => 'Free',
            'price_monthly_cents' => 0,
            'max_projects' => 1,
            'max_members' => 3,
            'max_storage_mb' => 200,
        ]);

        $this->superAdmin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'super@example.com',
            'password' => Hash::make('password'),
            'is_super' => true,
            'is_active' => true,
        ]);

        $this->regularAdmin = Admin::create([
            'name' => 'Regular Admin',
            'email' => 'regular@example.com',
            'password' => Hash::make('password'),
            'is_super' => false,
            'is_active' => true,
        ]);

        $this->user = User::factory()->create();
        $this->organization = Organization::factory()->create([
            'subscription_status' => 'active',
            'plan_id' => $this->freePlan->id,
        ]);
        $this->user->organizations()->attach($this->organization->id, ['role' => 'owner']);
        $this->user->update(['current_organization_id' => $this->organization->id]);
    }

    /**
     * Seed one row in every organization-scoped family. Returns the ids for
     * assertions.
     */
    protected function seedOrgData(Organization $org): array
    {
        $ids = [];
        $uid = $this->user->id;
        $orgId = $org->id;

        $project = Project::create(['organization_id' => $orgId, 'name' => 'Seed Project', 'status' => 'active']);
        $ids['project'] = $project->id;

        $ids['media'] = ProjectMedia::create([
            'project_id' => $project->id, 'uploaded_by' => $uid, 'disk' => 'public',
            'path' => 'seed/photo.png', 'original_name' => 'photo.png', 'mime' => 'image/png', 'size' => 123,
        ])->id;
        $ids['task'] = ProjectTask::create(['project_id' => $project->id, 'created_by' => $uid, 'title' => 'Seed task'])->id;
        $ids['issue'] = ProjectIssue::create(['project_id' => $project->id, 'created_by' => $uid, 'title' => 'Seed issue'])->id;
        $ids['log'] = ProjectLog::create(['project_id' => $project->id, 'user_id' => $uid, 'log_date' => now()->toDateString()])->id;
        $ids['cost'] = ProjectCost::create(['project_id' => $project->id, 'created_by' => $uid, 'cost_date' => now()->toDateString(), 'amount' => 100])->id;
        $ids['report'] = ProjectReport::create([
            'project_id' => $project->id, 'generated_by' => $uid,
            'from_date' => now()->toDateString(), 'to_date' => now()->toDateString(),
            'pdf_path' => 'seed/report.pdf', 'share_token' => 'seed-token-'.$orgId,
        ])->id;
        $ids['unit'] = ProjectUnit::create(['project_id' => $project->id, 'name' => 'Seed unit'])->id;
        $ids['today_log'] = TodayLog::create(['project_id' => $project->id, 'user_id' => $uid, 'log_date' => now()->toDateString()])->id;
        $ids['attachment'] = Attachment::create([
            'organization_id' => $orgId, 'project_id' => $project->id, 'uploaded_by' => $uid,
            'disk' => 'public', 'path' => 'seed/doc.pdf', 'original_name' => 'doc.pdf',
        ])->id;
        $ids['activity_log'] = ActivityLog::create([
            'organization_id' => $orgId, 'project_id' => $project->id, 'user_id' => $uid,
            'action' => 'created', 'entity_type' => 'projects', 'entity_id' => $project->id,
        ])->id;

        $ids['invite'] = OrganizationInvite::create([
            'organization_id' => $orgId, 'email' => 'invited@example.com',
            'token' => 'invite-token-'.$orgId, 'invited_by' => $uid,
        ])->id;
        $ids['note'] = OrganizationNote::create([
            'organization_id' => $orgId, 'admin_id' => $this->superAdmin->id, 'content' => 'Seed note',
        ])->id;
        $ids['org_task'] = OrganizationTask::create([
            'organization_id' => $orgId, 'created_by' => $this->superAdmin->id, 'content' => 'Seed org task',
        ])->id;
        $ids['owner_deal'] = OwnerDeal::create(['organization_id' => $orgId, 'title' => 'Seed owner deal'])->id;

        $payment = Payment::create([
            'organization_id' => $orgId, 'user_id' => $uid, 'plan_id' => $this->freePlan->id,
            'reference' => 'ref-'.$orgId, 'amount_cents' => 5000, 'status' => 'success',
        ]);
        $ids['payment'] = $payment->id;
        $ids['payment_note'] = PaymentNote::create([
            'payment_id' => $payment->id, 'admin_id' => $this->superAdmin->id, 'note' => 'Seed payment note',
        ])->id;

        $ids['announcement'] = Announcement::create([
            'organization_id' => $orgId, 'title' => 'Seed announcement', 'body' => 'Hello',
        ])->id;

        $ticket = SupportTicket::create([
            'organization_id' => $orgId, 'created_by_user_id' => $uid,
            'subject' => 'Seed ticket', 'message' => 'Help',
        ]);
        $ids['ticket'] = $ticket->id;
        $ids['ticket_note'] = SupportTicketNote::create([
            'support_ticket_id' => $ticket->id, 'admin_id' => $this->superAdmin->id, 'note' => 'Seed ticket note',
        ])->id;

        $ids['lead'] = Lead::create(['organization_id' => $orgId, 'first_name' => 'Seed', 'last_name' => 'Lead'])->id;
        $ids['deal'] = Deal::create(['organization_id' => $orgId, 'title' => 'Seed deal'])->id;

        $property = Property::create(['organization_id' => $orgId, 'name' => 'Seed property', 'type' => 'residential', 'status' => 'active']);
        $ids['crm_property'] = $property->id;
        $ids['crm_unit'] = PropertyUnit::create(['property_id' => $property->id, 'unit_number' => 'A1'])->id;

        $invoice = Invoice::create([
            'organization_id' => $orgId, 'number' => 'INV-SEED-'.$orgId, 'issue_date' => now()->toDateString(),
        ]);
        $ids['invoice'] = $invoice->id;
        $ids['invoice_payment'] = InvoicePayment::create([
            'invoice_id' => $invoice->id, 'amount_cents' => 1000, 'payment_date' => now()->toDateString(),
        ])->id;

        $ids['expense'] = Expense::create([
            'organization_id' => $orgId, 'incurred_date' => now()->toDateString(), 'description' => 'Seed expense',
        ])->id;
        $ids['budget'] = Budget::create(['organization_id' => $orgId, 'name' => 'Seed budget'])->id;

        $department = Department::create(['organization_id' => $orgId, 'name' => 'Seed dept']);
        $ids['department'] = $department->id;
        $employee = Employee::create([
            'organization_id' => $orgId, 'department_id' => $department->id,
            'first_name' => 'Seed', 'last_name' => 'Employee',
        ]);
        $ids['employee'] = $employee->id;
        $ids['payroll'] = Payroll::create([
            'organization_id' => $orgId, 'run_date' => now()->toDateString(),
            'start_date' => now()->toDateString(), 'end_date' => now()->toDateString(),
        ])->id;
        $ids['leave'] = Leave::create([
            'organization_id' => $orgId, 'employee_id' => $employee->id, 'type' => 'annual',
            'start_date' => now()->toDateString(), 'end_date' => now()->toDateString(),
        ])->id;

        // ERP properties module (no Eloquent model).
        $ids['erp_property'] = DB::table('properties')->insertGetId([
            'organization_id' => $orgId, 'name' => 'Seed ERP property',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $ids['erp_unit'] = DB::table('property_units')->insertGetId([
            'property_id' => $ids['erp_property'], 'unit_number' => 'B2',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // Page visit (no fillable on the model).
        $ids['page_visit'] = DB::table('page_visits')->insertGetId([
            'organization_id' => $orgId, 'user_id' => $uid,
            'url' => 'https://example.com/app/dashboard', 'path' => '/app/dashboard', 'method' => 'GET',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return $ids;
    }

    public function test_super_admin_can_soft_delete_organization_with_full_cascade()
    {
        $org = $this->organization;
        $ids = $this->seedOrgData($org);

        $response = $this->actingAs($this->superAdmin, 'owner')
            ->delete('/owner/organizations/'.$org->id);

        $response->assertRedirect(route('owner.organizations.index'));
        $response->assertSessionHas('success');

        // Organization itself is soft-deleted, not gone.
        $this->assertSoftDeleted('organizations', ['id' => $org->id]);

        // Every seeded family cascaded.
        foreach ([
            'projects' => 'project',
            'project_media' => 'media',
            'project_tasks' => 'task',
            'project_issues' => 'issue',
            'project_logs' => 'log',
            'project_costs' => 'cost',
            'project_reports' => 'report',
            'project_units' => 'unit',
            'today_logs' => 'today_log',
            'attachments' => 'attachment',
            'activity_logs' => 'activity_log',
            'organization_invites' => 'invite',
            'organization_notes' => 'note',
            'organization_tasks' => 'org_task',
            'owner_deals' => 'owner_deal',
            'payments' => 'payment',
            'payment_notes' => 'payment_note',
            'announcements' => 'announcement',
            'support_tickets' => 'ticket',
            'support_ticket_notes' => 'ticket_note',
            'leads' => 'lead',
            'deals' => 'deal',
            'crm_properties' => 'crm_property',
            'crm_property_units' => 'crm_unit',
            'invoices' => 'invoice',
            'invoice_payments' => 'invoice_payment',
            'expenses' => 'expense',
            'budgets' => 'budget',
            'departments' => 'department',
            'employees' => 'employee',
            'payrolls' => 'payroll',
            'leaves' => 'leave',
            'properties' => 'erp_property',
            'property_units' => 'erp_unit',
            'page_visits' => 'page_visit',
        ] as $table => $key) {
            $this->assertSoftDeleted($table, ['id' => $ids[$key]], "expected soft delete in {$table}");
        }

        // Membership pivots are stamped too.
        $this->assertNotNull(DB::table('organization_user')
            ->where('organization_id', $org->id)
            ->where('user_id', $this->user->id)
            ->value('deleted_at'));
    }

    public function test_trashed_organization_data_is_excluded_from_normal_queries()
    {
        $org = $this->organization;
        $this->seedOrgData($org);

        $this->actingAs($this->superAdmin, 'owner')
            ->delete('/owner/organizations/'.$org->id);

        $this->assertNull(Organization::find($org->id));
        $this->assertSame(0, Project::where('organization_id', $org->id)->count());
        $this->assertSame(0, Organization::count());
        $this->assertSame(1, Organization::onlyTrashed()->count());

        // The member no longer resolves the trashed org as current.
        $this->assertSame(0, $this->user->organizations()->count());
    }

    public function test_restore_brings_back_everything_deleted_by_the_cascade()
    {
        $org = $this->organization;
        $ids = $this->seedOrgData($org);

        $this->actingAs($this->superAdmin, 'owner')
            ->delete('/owner/organizations/'.$org->id);

        $response = $this->actingAs($this->superAdmin, 'owner')
            ->post('/owner/organizations/'.$org->id.'/restore');

        $response->assertRedirect(route('owner.organizations.index'));
        $response->assertSessionHas('success');

        $this->assertNotNull(Organization::find($org->id));

        foreach ([
            'projects' => 'project',
            'project_media' => 'media',
            'project_tasks' => 'task',
            'project_issues' => 'issue',
            'project_logs' => 'log',
            'project_costs' => 'cost',
            'project_reports' => 'report',
            'project_units' => 'unit',
            'today_logs' => 'today_log',
            'attachments' => 'attachment',
            'activity_logs' => 'activity_log',
            'organization_invites' => 'invite',
            'organization_notes' => 'note',
            'organization_tasks' => 'org_task',
            'owner_deals' => 'owner_deal',
            'payments' => 'payment',
            'payment_notes' => 'payment_note',
            'announcements' => 'announcement',
            'support_tickets' => 'ticket',
            'support_ticket_notes' => 'ticket_note',
            'leads' => 'lead',
            'deals' => 'deal',
            'crm_properties' => 'crm_property',
            'crm_property_units' => 'crm_unit',
            'invoices' => 'invoice',
            'invoice_payments' => 'invoice_payment',
            'expenses' => 'expense',
            'budgets' => 'budget',
            'departments' => 'department',
            'employees' => 'employee',
            'payrolls' => 'payroll',
            'leaves' => 'leave',
            'properties' => 'erp_property',
            'property_units' => 'erp_unit',
            'page_visits' => 'page_visit',
        ] as $table => $key) {
            $this->assertDatabaseHas($table, ['id' => $ids[$key], 'deleted_at' => null], "expected restore in {$table}");
        }

        // Membership pivot restored as well.
        $this->assertNull(DB::table('organization_user')
            ->where('organization_id', $org->id)
            ->where('user_id', $this->user->id)
            ->value('deleted_at'));
        $this->assertSame(1, $this->user->organizations()->count());
    }

    public function test_restore_does_not_resurrect_rows_deleted_before_the_organization()
    {
        $org = $this->organization;
        $ids = $this->seedOrgData($org);

        // Independently delete a task and a note long before the org deletion.
        $oldTask = ProjectTask::find($ids['task']);
        $oldTask->delete();
        $oldTask->update(['deleted_at' => now()->subDay()]);

        $oldNote = OrganizationNote::find($ids['note']);
        $oldNote->delete();
        $oldNote->update(['deleted_at' => now()->subDay()]);

        $this->actingAs($this->superAdmin, 'owner')
            ->delete('/owner/organizations/'.$org->id);

        $this->actingAs($this->superAdmin, 'owner')
            ->post('/owner/organizations/'.$org->id.'/restore');

        // The independently deleted rows stay deleted.
        $this->assertSoftDeleted('project_tasks', ['id' => $ids['task']]);
        $this->assertSoftDeleted('organization_notes', ['id' => $ids['note']]);

        // …while a cascade-deleted sibling of the same table is restored.
        $this->assertDatabaseHas('project_media', ['id' => $ids['media'], 'deleted_at' => null]);
    }

    public function test_soft_delete_does_not_remove_uploaded_files()
    {
        Storage::fake('public');

        $org = $this->organization;
        $ids = $this->seedOrgData($org);

        $path = 'seed/photo.png';
        Storage::disk('public')->put($path, 'fake-image-bytes');

        $this->actingAs($this->superAdmin, 'owner')
            ->delete('/owner/organizations/'.$org->id);

        // The storage object is untouched; only the DB row is stamped.
        $this->assertTrue(Storage::disk('public')->exists($path));
        $this->assertSoftDeleted('project_media', ['id' => $ids['media']]);
        $this->assertSoftDeleted('attachments', ['id' => $ids['attachment']]);
    }

    public function test_non_super_admin_cannot_delete_or_restore_organization()
    {
        $org = $this->organization;
        $ids = $this->seedOrgData($org);

        $response = $this->actingAs($this->regularAdmin, 'owner')
            ->delete('/owner/organizations/'.$org->id);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertNotSoftDeleted('organizations', ['id' => $org->id]);
        $this->assertDatabaseHas('projects', ['id' => $ids['project'], 'deleted_at' => null]);

        // And cannot restore either.
        $this->actingAs($this->superAdmin, 'owner')
            ->delete('/owner/organizations/'.$org->id);

        $response = $this->actingAs($this->regularAdmin, 'owner')
            ->post('/owner/organizations/'.$org->id.'/restore');

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertSoftDeleted('organizations', ['id' => $org->id]);
    }

    public function test_guests_are_redirected_from_owner_organization_routes()
    {
        $this->get('/owner/organizations/trash')->assertRedirect(route('owner.login'));
        $this->delete('/owner/organizations/1')->assertRedirect(route('owner.login'));
        $this->post('/owner/organizations/1/restore')->assertRedirect(route('owner.login'));
    }

    public function test_trash_view_lists_deleted_organizations_with_deleted_at()
    {
        $org = $this->organization;

        $this->actingAs($this->superAdmin, 'owner')
            ->delete('/owner/organizations/'.$org->id);

        $response = $this->actingAs($this->superAdmin, 'owner')
            ->get('/owner/organizations/trash');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Owner/Organizations/Trash')
            ->has('organizations.data', 1)
            ->where('organizations.data.0.id', $org->id)
            ->whereNotNull('organizations.data.0.deleted_at'));
    }
}
