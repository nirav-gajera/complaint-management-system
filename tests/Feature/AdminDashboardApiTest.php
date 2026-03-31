<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminDashboardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_supports_trend_period_filters(): void
    {
        Carbon::setTestNow('2026-03-23 10:15:00');

        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);
        $category = ComplaintCategory::factory()->create();

        Complaint::factory()->create([
            'complaint_category_id' => $category->id,
            'created_at' => '2026-03-23 10:05:00',
            'updated_at' => '2026-03-23 10:05:00',
        ]);
        Complaint::factory()->create([
            'complaint_category_id' => $category->id,
            'created_at' => '2026-03-23 10:45:00',
            'updated_at' => '2026-03-23 10:45:00',
        ]);
        Complaint::factory()->create([
            'complaint_category_id' => $category->id,
            'created_at' => '2026-03-22 09:00:00',
            'updated_at' => '2026-03-22 09:00:00',
        ]);
        Complaint::factory()->create([
            'complaint_category_id' => $category->id,
            'created_at' => '2026-03-01 08:00:00',
            'updated_at' => '2026-03-01 08:00:00',
        ]);
        Complaint::factory()->create([
            'complaint_category_id' => $category->id,
            'created_at' => '2026-01-15 08:00:00',
            'updated_at' => '2026-01-15 08:00:00',
        ]);
        Complaint::factory()->create([
            'complaint_category_id' => $category->id,
            'created_at' => '2025-12-31 23:00:00',
            'updated_at' => '2025-12-31 23:00:00',
        ]);

        $dayResponse = $this->actingAs($admin)->getJson('/api/admin/dashboard?period=day');

        $dayResponse->assertOk()
            ->assertJsonPath('selected_period', 'day')
            ->assertJsonCount(24, 'trend');
        $this->assertSame(2, collect($dayResponse->json('trend'))->firstWhere('key', '2026-03-23 10:00')['total']);

        $weekResponse = $this->actingAs($admin)->getJson('/api/admin/dashboard?period=week');

        $weekResponse->assertOk()
            ->assertJsonPath('selected_period', 'week')
            ->assertJsonCount(7, 'trend');
        $this->assertSame(1, collect($weekResponse->json('trend'))->firstWhere('key', '2026-03-22')['total']);
        $this->assertSame(2, collect($weekResponse->json('trend'))->firstWhere('key', '2026-03-23')['total']);

        $monthResponse = $this->actingAs($admin)->getJson('/api/admin/dashboard?period=month');

        $monthResponse->assertOk()
            ->assertJsonPath('selected_period', 'month');
        $this->assertSame(1, collect($monthResponse->json('trend'))->firstWhere('key', '2026-03-01')['total']);
        $this->assertSame(2, collect($monthResponse->json('trend'))->firstWhere('key', '2026-03-23')['total']);

        $yearResponse = $this->actingAs($admin)->getJson('/api/admin/dashboard?period=year');

        $yearResponse->assertOk()
            ->assertJsonPath('selected_period', 'year')
            ->assertJsonCount(12, 'trend');
        $this->assertSame(1, collect($yearResponse->json('trend'))->firstWhere('key', '2026-01')['total']);
        $this->assertSame(4, collect($yearResponse->json('trend'))->firstWhere('key', '2026-03')['total']);

        Carbon::setTestNow();
    }

    public function test_admin_dashboard_rejects_invalid_trend_period(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin)
            ->getJson('/api/admin/dashboard?period=quarter')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['period']);
    }
}
