<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_delete_category_that_has_complaints(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);
        $category = ComplaintCategory::factory()->create();
        Complaint::factory()->create([
            'complaint_category_id' => $category->id,
        ]);

        $response = $this->actingAs($admin)->deleteJson("/api/admin/categories/{$category->id}");

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'This category cannot be deleted because complaints are using it.',
            ]);

        $this->assertDatabaseHas('complaint_categories', [
            'id' => $category->id,
        ]);
    }

    public function test_admin_can_delete_unused_category(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);
        $category = ComplaintCategory::factory()->create();

        $this->actingAs($admin)->deleteJson("/api/admin/categories/{$category->id}")
            ->assertOk()
            ->assertJson([
                'message' => 'Category deleted successfully.',
            ]);

        $this->assertDatabaseMissing('complaint_categories', [
            'id' => $category->id,
        ]);
    }
}