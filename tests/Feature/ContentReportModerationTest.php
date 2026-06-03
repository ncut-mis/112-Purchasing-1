<?php

use App\Models\Admin;
use App\Models\AgentPost;
use App\Models\ContentReport;
use App\Models\RequestList;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

function createAdminForContentReportModeration(): Admin
{
    return Admin::create([
        'name' => 'Moderation Admin',
        'username' => 'moderation_admin_' . uniqid(),
        'email' => 'moderation-admin-' . uniqid() . '@example.com',
        'password' => Hash::make('password'),
    ]);
}

test('approving an agent post report soft deletes the reported agent post without removing its row', function () {
    $admin = createAdminForContentReportModeration();
    $owner = User::factory()->create();
    $reporter = User::factory()->create();

    $agentPost = AgentPost::create([
        'user_id' => $owner->id,
        'title' => '違規代購團',
        'country' => '日本',
        'city' => '東京',
        'description' => '疑似違規內容',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addDays(7)->toDateString(),
        'estimated_shipping_date' => now()->addDays(14)->toDateString(),
        'status' => 'open',
    ]);

    $report = ContentReport::create([
        'reporter_id' => $reporter->id,
        'reportable_type' => AgentPost::class,
        'reportable_id' => $agentPost->id,
        'report_type' => 'fraud',
        'reason' => '疑似詐騙',
        'status' => ContentReport::STATUS_PENDING,
    ]);

    $response = $this->actingAs($admin, 'admin')
        ->patch(route('admin.reports.approve', $report));

    $response->assertRedirect(route('admin.dashboard', ['tab' => 'violation'], false));
    $report->refresh();

    expect($report->status)->toBe(ContentReport::STATUS_APPROVED);
    expect(AgentPost::find($agentPost->id))->toBeNull();
    expect(AgentPost::withTrashed()->find($agentPost->id))->not->toBeNull();
    expect(AgentPost::withTrashed()->find($agentPost->id)->trashed())->toBeTrue();
});

test('approving a request list report soft deletes the reported request list without removing its row', function () {
    $admin = createAdminForContentReportModeration();
    $owner = User::factory()->create();
    $reporter = User::factory()->create();

    $requestList = RequestList::create([
        'user_id' => $owner->id,
        'title' => '違規請託單',
        'store_name' => '測試店家',
        'country' => '韓國',
        'deadline' => now()->addDays(10)->toDateString(),
        'budget_total' => 1000,
        'currency' => 'TWD',
        'status' => 'pending',
        'detail_address' => '測試地址',
        'note' => '疑似違規內容',
    ]);

    $report = ContentReport::create([
        'reporter_id' => $reporter->id,
        'reportable_type' => RequestList::class,
        'reportable_id' => $requestList->id,
        'report_type' => 'prohibited_items',
        'reason' => '疑似違禁品',
        'status' => ContentReport::STATUS_PENDING,
    ]);

    $response = $this->actingAs($admin, 'admin')
        ->patch(route('admin.reports.approve', $report));

    $response->assertRedirect(route('admin.dashboard', ['tab' => 'violation'], false));
    $report->refresh();

    expect($report->status)->toBe(ContentReport::STATUS_APPROVED);
    expect(RequestList::find($requestList->id))->toBeNull();
    expect(RequestList::withTrashed()->find($requestList->id))->not->toBeNull();
    $trashedRequestList = RequestList::withTrashed()->find($requestList->id);

    expect($trashedRequestList->trashed())->toBeTrue();
    expect($trashedRequestList->violation_notified_at)->not->toBeNull();
    expect($trashedRequestList->violation_notice_read_at)->toBeNull();
    expect($trashedRequestList->violation_notice_removed_at)->toBeNull();
});

test('overriding an approved report to rejected restores the soft deleted reportable', function () {
    $admin = createAdminForContentReportModeration();
    $owner = User::factory()->create();
    $reporter = User::factory()->create();

    $agentPost = AgentPost::create([
        'user_id' => $owner->id,
        'title' => '可恢復代購團',
        'country' => '日本',
        'city' => '大阪',
        'description' => '原本被檢舉',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addDays(7)->toDateString(),
        'estimated_shipping_date' => now()->addDays(14)->toDateString(),
        'status' => 'open',
    ]);

    $agentPost->delete();

    $report = ContentReport::create([
        'reporter_id' => $reporter->id,
        'reportable_type' => AgentPost::class,
        'reportable_id' => $agentPost->id,
        'report_type' => 'fraud',
        'reason' => '疑似詐騙',
        'status' => ContentReport::STATUS_APPROVED,
        'reviewed_by_admin_id' => $admin->id,
        'reviewed_at' => now(),
    ]);

    $response = $this->actingAs($admin, 'admin')
        ->patch(route('admin.reports.override', $report), ['decision' => 'rejected']);

    $response->assertRedirect(route('admin.dashboard', ['tab' => 'violation'], false));
    $report->refresh();

    expect($report->status)->toBe(ContentReport::STATUS_REJECTED);
    expect(AgentPost::find($agentPost->id))->not->toBeNull();
    expect(AgentPost::withTrashed()->find($agentPost->id)->trashed())->toBeFalse();
});
