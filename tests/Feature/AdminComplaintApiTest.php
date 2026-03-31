<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminComplaintApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_complaint_status_and_assignment(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);
        $agent = User::factory()->create([
            'role' => User::ROLE_SUPPORT_AGENT,
            'email_verified_at' => now(),
        ]);
        $category = ComplaintCategory::factory()->create();
        $complaint = Complaint::factory()->create([
            'complaint_category_id' => $category->id,
        ]);

        $response = $this->actingAs($admin)->patchJson("/api/admin/complaints/{$complaint->id}", [
            'status' => Complaint::STATUS_IN_PROGRESS,
            'assigned_to' => $agent->id,
            'public_message' => 'We have started reviewing your complaint.',
            'internal_note' => 'Initial triage completed.',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', Complaint::STATUS_IN_PROGRESS)
            ->assertJsonPath('data.assignee.id', $agent->id);

        $this->assertDatabaseHas('complaint_messages', [
            'complaint_id' => $complaint->id,
            'is_internal' => false,
        ]);
    }

    public function test_admin_complaint_index_supports_search_and_status_filters(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);
        $category = ComplaintCategory::factory()->create();

        $matchingComplaint = Complaint::factory()->create([
            'complaint_category_id' => $category->id,
            'status' => Complaint::STATUS_PENDING,
            'title' => 'Searchable title',
        ]);

        Complaint::factory()->create([
            'complaint_category_id' => $category->id,
            'status' => Complaint::STATUS_RESOLVED,
            'title' => 'Resolved title',
        ]);

        $response = $this->actingAs($admin)->getJson('/api/admin/complaints?status=pending&search=Searchable');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matchingComplaint->id);
    }

    public function test_admin_public_response_is_visible_on_complaint_detail(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);
        $category = ComplaintCategory::factory()->create();
        $complaint = Complaint::factory()->create([
            'complaint_category_id' => $category->id,
        ]);

        $this->actingAs($admin)->patchJson("/api/admin/complaints/{$complaint->id}", [
            'status' => Complaint::STATUS_IN_PROGRESS,
            'public_message' => 'We need one more document from you.',
            'internal_note' => 'Requested additional evidence.',
        ])->assertOk();

        $response = $this->actingAs($admin)->getJson("/api/admin/complaints/{$complaint->id}");

        $response->assertOk()
            ->assertJsonPath('data.messages.0.message', 'We need one more document from you.')
            ->assertJsonPath('data.messages.1.message', 'Requested additional evidence.');
    }
}