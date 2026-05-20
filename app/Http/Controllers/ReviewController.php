<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Order;
use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reviewable_type' => 'required|in:order,quote',
            'reviewable_id'   => 'required|integer',
            'rating'          => 'required|integer|min:1|max:5',
            'comment'         => 'nullable|string|max:500',
        ]);

        $userId = Auth::id();

        if ($validated['reviewable_type'] === 'order') {
            $source = Order::where('id', $validated['reviewable_id'])
                ->where('buyer_id', $userId)
                ->where('status', 'completed')
                ->firstOrFail();
            $reviewableType = Order::class;
            $revieweeId = $source->seller_id;
        } else {
            $source = Quote::where('id', $validated['reviewable_id'])
                ->where('status', 'completed')
                ->firstOrFail();
            // 確認是請託人才能評價
            if ($source->requestList->user_id !== $userId) {
                abort(403, '您沒有權限評價此訂單');
            }
            $reviewableType = Quote::class;
            $revieweeId = $source->user_id;
        }

        // 防止重複評價
        $exists = Review::where('reviewer_id', $userId)
            ->where('reviewable_type', $reviewableType)
            ->where('reviewable_id', $source->id)
            ->exists();

        if ($exists) {
            return back()->with('error', '您已經評價過此訂單了。');
        }

        Review::create([
            'reviewer_id'     => $userId,
            'reviewee_id'     => $revieweeId,
            'reviewable_type' => $reviewableType,
            'reviewable_id'   => $source->id,
            'rating'          => $validated['rating'],
            'comment'         => $validated['comment'] ?? null,
        ]);

        return back()->with('success', '評價已送出，感謝您的回饋！');
    }
}