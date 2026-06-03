<?php

namespace App\Http\Controllers;

use App\Models\AgentApplication;
use App\Models\AgentPost;
use App\Models\ContentReport;
use App\Models\RequestItem;
use App\Models\RequestList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        return redirect()->route('login');
    }

    public function login(Request $request)
    {
        return redirect()->route('login');
    }

    public function dashboard(Request $request)
    {
         $adminName = Auth::guard('admin')->user()?->name ?? $request->session()->get('admin_auth_name');
        $agentApplications = AgentApplication::with('user')->latest()->take(10)->get();
        $requestLists = RequestList::with(['items', 'user'])->latest()->take(10)->get();
        $posts = AgentPost::with(['user', 'products'])->latest()->take(10)->get();
        $requestListReports = ContentReport::with(['reporter', 'reportable'])
            ->where('reportable_type', RequestList::class)
            ->latest()
            ->take(50)
            ->get();
        $agentPostReports = ContentReport::with(['reporter', 'reportable'])
            ->where('reportable_type', AgentPost::class)
            ->latest()
            ->take(50)
            ->get();

        return view('admin.dashboard', compact(
            'adminName',
            'agentApplications',
            'requestLists',
            'posts',
            'requestListReports',
            'agentPostReports'
        ));
    }


   public function approveReport(ContentReport $report)
    {
        if ($report->status !== ContentReport::STATUS_PENDING) {
            return redirect()->route('admin.dashboard')->with('status', '此檢舉案件已審核過');
        }

        DB::transaction(function () use ($report) {
            $report->update([
                'status' => ContentReport::STATUS_APPROVED,
                'reviewed_by_admin_id' => Auth::guard('admin')->id(),
                'reviewed_at' => now(),
            ]);

            $report->removeReportableFromPublicListings();
        });

        return redirect()->route('admin.dashboard', ['tab' => 'violation'])->with('status', '已標記為檢舉成立，檢舉內容已從前台移除');
    }

    public function rejectReport(ContentReport $report)
    {
        if ($report->status !== ContentReport::STATUS_PENDING) {
            return redirect()->route('admin.dashboard')->with('status', '此檢舉案件已審核過');
        }

        $report->update([
            'status' => ContentReport::STATUS_REJECTED,
            'reviewed_by_admin_id' => Auth::guard('admin')->id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'violation'])->with('status', '已標記為檢舉不成立');
    }

    public function overrideDecision(ContentReport $report)
    {
        if (!in_array($report->status, [ContentReport::STATUS_APPROVED, ContentReport::STATUS_REJECTED])) {
            return redirect()->route('admin.dashboard')->with('status', '只能更改已完成的判定');
        }

        $validated = request()->validate([
            'decision' => ['required', 'in:approved,rejected'],
        ]);

       $newStatus = $validated['decision'] === 'approved' ? ContentReport::STATUS_APPROVED : ContentReport::STATUS_REJECTED;

        DB::transaction(function () use ($report, $newStatus) {
            $report->update([
                'status' => $newStatus,
                'reviewed_by_admin_id' => Auth::guard('admin')->id(),
                'reviewed_at' => now(),
            ]);

            if ($newStatus === ContentReport::STATUS_APPROVED) {
                $report->removeReportableFromPublicListings();
            } else {
                $report->restoreReportableToPublicListings();
            }
        });

        $statusMessage = $newStatus === ContentReport::STATUS_APPROVED ? '已更改為檢舉成立，檢舉內容已從前台移除' : '已更改為檢舉不成立，檢舉內容已恢復顯示';

        return redirect()->route('admin.dashboard', ['tab' => 'violation'])->with('status', $statusMessage);
    }

    public function approveAgentApplication(AgentApplication $agentApplication)
    {
        if (! in_array($agentApplication->status, ['pending', 'resubmitted'])) {
            return redirect()->route('admin.dashboard')->with('status', '此申請已完成審核');
        }

        $agentApplication->update(['status' => 'approved']);

        return redirect()->route('admin.dashboard')->with('status', '已審核通過代購人申請');
    }

    public function rejectAgentApplication(AgentApplication $agentApplication)
    {
        if (! in_array($agentApplication->status, ['pending', 'resubmitted'])) {
            return redirect()->route('admin.dashboard')->with('status', '此申請已完成審核');
        }

        $agentApplication->update(['status' => 'rejected']);

        return redirect()->route('admin.dashboard')->with('status', '已審核不通過代購人申請');
    }

    public function deleteRequestList(RequestList $requestList)
    {
        $requestList->load('items');

        foreach ($requestList->items as $item) {
            if ($item->reference_image) {
                Storage::disk('public')->delete($item->reference_image);
            }
        }

        $requestList->forceDelete();

        return redirect()->route('admin.dashboard')->with('status', '請託單已刪除');
    }

    public function deleteAgentPost(AgentPost $agentPost)
    {
        $agentPost->load('products');

        DB::transaction(function () use ($agentPost) {
            foreach ($agentPost->products as $product) {
                if ($product->image_path) {
                    Storage::disk('public')->delete($product->image_path);
                }
                $product->delete();
            }

            if ($agentPost->cover_image) {
                Storage::disk('public')->delete($agentPost->cover_image);
            }

            $agentPost->forceDelete();
        });

        return redirect()->route('admin.dashboard')->with('status', '代購團已刪除');
    }

    public function identityImage(int $user, string $side)
    {
        if (! in_array($side, ['front', 'back'], true)) {
            abort(404);
        }

        $agentApplication = AgentApplication::where('user_id', $user)
            ->latest('id')
            ->first();

        if (! $agentApplication) {
            abort(404);
        }

        $rawPath = $side === 'front'
            ? $agentApplication->id_image_front
            : $agentApplication->id_image_back;

        if (! $rawPath) {
            abort(404);
        }

        if (is_string($rawPath)) {
            $normalized = ltrim($rawPath, '/');

            if (Str::startsWith($normalized, 'storage/')) {
                $normalized = Str::after($normalized, 'storage/');
            }

            if (Str::startsWith($normalized, 'public/')) {
                $normalized = Str::after($normalized, 'public/');
            }

            if (Storage::disk('public')->exists($normalized)) {
                return response()->file(Storage::disk('public')->path($normalized));
            }
        }

        $binary = is_resource($rawPath) ? stream_get_contents($rawPath) : $rawPath;

        if (! is_string($binary) || $binary === '') {
            abort(404);
        }

        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->buffer($binary) ?: 'application/octet-stream';

        return response($binary, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="identity-' . $agentApplication->id . '-' . $side . '"',
        ]);
    }

    public function requestItemImage(RequestItem $requestItem)
    {
        $rawPath = $requestItem->reference_image;

        if (! $rawPath) {
            abort(404);
        }

        if (is_string($rawPath)) {
            $normalized = ltrim($rawPath, '/');

            if (Str::startsWith($normalized, 'storage/')) {
                $normalized = Str::after($normalized, 'storage/');
            }

            if (Str::startsWith($normalized, 'public/')) {
                $normalized = Str::after($normalized, 'public/');
            }

            if (Storage::disk('public')->exists($normalized)) {
                return response()->file(Storage::disk('public')->path($normalized));
            }
        }

        $binary = is_resource($rawPath) ? stream_get_contents($rawPath) : $rawPath;

        if (! is_string($binary) || $binary === '') {
            abort(404);
        }

        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->buffer($binary) ?: 'application/octet-stream';

        return response($binary, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="identity-' . $agentApplication->id . '-' . $side . '"',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->forget(['admin_auth_id', 'admin_auth_name']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();
         return redirect()->route('login');
    }
}