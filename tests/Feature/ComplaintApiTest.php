<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComplaintApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_user_can_create_complaint(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $category = ComplaintCategory::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/complaints', [
            'title' => 'Service delay issue',
            'complaint_category_id' => $category->id,
            'description' => 'The service request has been pending for more than two weeks without any update.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Service delay issue');

        $this->assertDatabaseHas('complaints', [
            'user_id' => $user->id,
            'title' => 'Service delay issue',
        ]);
    }

    public function test_inactive_user_cannot_create_complaint(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => false,
        ]);
        $category = ComplaintCategory::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/complaints', [
            'title' => 'Service delay issue',
            'complaint_category_id' => $category->id,
            'description' => 'The service request has been pending for more than two weeks without any update.',
        ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('complaints', [
            'user_id' => $user->id,
            'title' => 'Service delay issue',
        ]);
    }

    public function test_owner_can_update_pending_complaint_before_admin_action(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $category = ComplaintCategory::factory()->create();
        $complaint = Complaint::factory()->create([
            'user_id' => $user->id,
            'complaint_category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->putJson("/api/complaints/{$complaint->id}", [
            'title' => 'Updated title',
            'complaint_category_id' => $category->id,
            'description' => 'Updated complaint description with enough characters to pass validation.',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.title', 'Updated title');
    }

    public function test_complaint_index_returns_only_owned_records_and_applies_filters(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $otherUser = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $serviceCategory = ComplaintCategory::factory()->create(['name' => 'Service']);
        $productCategory = ComplaintCategory::factory()->create(['name' => 'Product']);

        $matchingComplaint = Complaint::factory()->create([
            'user_id' => $user->id,
            'complaint_category_id' => $serviceCategory->id,
            'status' => Complaint::STATUS_PENDING,
            'title' => 'Phone service outage',
            'description' => 'The service has been unavailable all week.',
        ]);

        Complaint::factory()->create([
            'user_id' => $user->id,
            'complaint_category_id' => $productCategory->id,
            'status' => Complaint::STATUS_RESOLVED,
            'title' => 'Resolved product issue',
        ]);

        Complaint::factory()->create([
            'user_id' => $otherUser->id,
            'complaint_category_id' => $serviceCategory->id,
            'status' => Complaint::STATUS_PENDING,
            'title' => 'Another user complaint',
        ]);

        $response = $this->actingAs($user)->getJson('/api/complaints?status=pending&category_id='.$serviceCategory->id.'&search=Phone');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matchingComplaint->id);
    }

    public function test_inactive_user_cannot_view_complaint_index(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)->getJson('/api/complaints');

        $response->assertForbidden();
    }

    public function test_dashboard_returns_recent_complaints_for_authenticated_user(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $category = ComplaintCategory::factory()->create();

        $newestComplaint = Complaint::factory()->create([
            'user_id' => $user->id,
            'complaint_category_id' => $category->id,
            'title' => 'Newest complaint',
            'created_at' => now(),
        ]);

        Complaint::factory()->create([
            'user_id' => $user->id,
            'complaint_category_id' => $category->id,
            'title' => 'Older complaint',
            'created_at' => now()->subDay(),
        ]);

        Complaint::factory()->create([
            'complaint_category_id' => $category->id,
            'created_at' => now()->addSecond(),
        ]);

        $response = $this->actingAs($user)->getJson('/api/dashboard');

        $response->assertOk()
            ->assertJsonPath('stats.total', 2)
            ->assertJsonPath('recent_complaints.0.id', $newestComplaint->id);
    }
}
