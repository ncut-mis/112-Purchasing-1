@php
    use Illuminate\Support\Facades\Auth;
    $userId = Auth::id();

    // 已完成的跟單（代購團）- Order
    $completedOrders = \App\Models\Order::where('buyer_id', $userId)
        ->where('status', 'completed')
        ->with(['seller:id,name', 'source'])
        ->latest('updated_at')
        ->get();

    // 已完成的請託單報價 - Quote
    $completedQuotes = \App\Models\Quote::where('status', 'completed')
        ->whereHas('requestList', fn($q) => $q->where('user_id', $userId))
        ->with(['user:id,name', 'requestList:id,title,user_id'])
        ->latest('updated_at')
        ->get();

    // 已評價的紀錄
    $reviewedOrderIds = \App\Models\Review::where('reviewer_id', $userId)
        ->where('reviewable_type', \App\Models\Order::class)
        ->pluck('reviewable_id')->toArray();

    $reviewedQuoteIds = \App\Models\Review::where('reviewer_id', $userId)
        ->where('reviewable_type', \App\Models\Quote::class)
        ->pluck('reviewable_id')->toArray();
@endphp

<div class="bg-white rounded-xl shadow-sm p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-1">評價中心</h2>
    <p class="text-sm text-gray-500 mb-6">針對已完成的訂單，為代購人留下評價。</p>

    {{-- Tab 切換 --}}
    <div x-data="{ reviewTab: 'orders' }">
        <div class="flex gap-2 mb-6 border-b border-gray-100 pb-3">
            <button type="button"
                @click="reviewTab = 'orders'"
                :class="reviewTab === 'orders' ? 'bg-purple-50 text-purple-700 border-purple-300 font-bold' : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50'"
                class="px-4 py-1.5 rounded-full border text-xs transition">
                跟單評價
            </button>
            <button type="button"
                @click="reviewTab = 'quotes'"
                :class="reviewTab === 'quotes' ? 'bg-green-50 text-green-700 border-green-300 font-bold' : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50'"
                class="px-4 py-1.5 rounded-full border text-xs transition">
                請託單評價
            </button>
        </div>

        {{-- 跟單評價（代購團） --}}
        <div x-show="reviewTab === 'orders'" class="space-y-4">
            @forelse($completedOrders as $order)
                @php
                    $hasReviewed = in_array($order->id, $reviewedOrderIds);
                    $existingReview = $hasReviewed
                        ? \App\Models\Review::where('reviewer_id', $userId)
                            ->where('reviewable_type', \App\Models\Order::class)
                            ->where('reviewable_id', $order->id)
                            ->first()
                        : null;
                @endphp
                <div class="rounded-xl border {{ $hasReviewed ? 'border-gray-100 bg-gray-50' : 'border-purple-100 bg-purple-50/30' }} p-4">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="text-sm font-bold text-gray-800">{{ $order->source->title ?? '代購團' }}</h4>
                                @if($hasReviewed)
                                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-bold bg-gray-100 text-gray-500 border-gray-200">已評價</span>
                                @else
                                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-bold bg-purple-50 text-purple-600 border-purple-200">待評價</span>
                                @endif
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                代購人：{{ $order->seller->name ?? '-' }} ・
                                訂單金額：NT$ {{ number_format($order->total_amount, 0) }}
                            </p>
                            @if($hasReviewed && $existingReview)
                                <div class="mt-2 flex items-center gap-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= $existingReview->rating ? 'text-amber-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                    @if($existingReview->comment)
                                        <span class="ml-2 text-xs text-gray-500">「{{ $existingReview->comment }}」</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        @if(!$hasReviewed)
                            <button type="button"
                                onclick="openReviewModal('order', {{ $order->id }}, '{{ addslashes($order->seller->name ?? '代購人') }}', '{{ addslashes($order->source->title ?? '代購團') }}')"
                                class="shrink-0 inline-flex items-center rounded-lg bg-purple-600 px-3 py-2 text-xs font-bold text-white hover:bg-purple-700 transition">
                                ✦ 寫評價
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-gray-400 text-sm text-center py-10 border border-dashed border-gray-200 rounded-xl">
                    目前沒有可評價的跟單紀錄。
                </div>
            @endforelse
        </div>

        {{-- 請託單評價（報價代購人） --}}
        <div x-show="reviewTab === 'quotes'" class="space-y-4">
            @forelse($completedQuotes as $quote)
                @php
                    $hasReviewed = in_array($quote->id, $reviewedQuoteIds);
                    $existingReview = $hasReviewed
                        ? \App\Models\Review::where('reviewer_id', $userId)
                            ->where('reviewable_type', \App\Models\Quote::class)
                            ->where('reviewable_id', $quote->id)
                            ->first()
                        : null;
                @endphp
                <div class="rounded-xl border {{ $hasReviewed ? 'border-gray-100 bg-gray-50' : 'border-green-100 bg-green-50/30' }} p-4">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="text-sm font-bold text-gray-800">{{ $quote->requestList->title ?? '請託單' }}</h4>
                                @if($hasReviewed)
                                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-bold bg-gray-100 text-gray-500 border-gray-200">已評價</span>
                                @else
                                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-bold bg-green-50 text-green-600 border-green-200">待評價</span>
                                @endif
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                代購人：{{ $quote->user->name ?? '-' }} ・
                                報價金額：NT$ {{ number_format($quote->price, 0) }}
                            </p>
                            @if($hasReviewed && $existingReview)
                                <div class="mt-2 flex items-center gap-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= $existingReview->rating ? 'text-amber-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                    @if($existingReview->comment)
                                        <span class="ml-2 text-xs text-gray-500">「{{ $existingReview->comment }}」</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        @if(!$hasReviewed)
                            <button type="button"
                                onclick="openReviewModal('quote', {{ $quote->id }}, '{{ addslashes($quote->user->name ?? '代購人') }}', '{{ addslashes($quote->requestList->title ?? '請託單') }}')"
                                class="shrink-0 inline-flex items-center rounded-lg bg-green-600 px-3 py-2 text-xs font-bold text-white hover:bg-green-700 transition">
                                ✦ 寫評價
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-gray-400 text-sm text-center py-10 border border-dashed border-gray-200 rounded-xl">
                    目前沒有可評價的請託單紀錄。
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- 評價 Modal --}}
<div id="review-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4"
     onclick="if(event.target===this){closeReviewModal();}">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl p-6">
        <div class="flex items-center justify-between mb-4">
            <h4 class="text-lg font-bold text-gray-800">寫下評價</h4>
            <button type="button" onclick="closeReviewModal()" class="text-2xl text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <div class="mb-4 p-3 bg-gray-50 rounded-xl">
            <p class="text-xs text-gray-500">評價對象</p>
            <p id="review-modal-reviewee" class="text-sm font-bold text-gray-800 mt-0.5"></p>
            <p id="review-modal-source" class="text-xs text-gray-500 mt-0.5"></p>
        </div>
        <form id="review-form" method="POST" action="{{ route('reviews.store') }}">
            @csrf
            <input type="hidden" name="reviewable_type" id="review-type-input">
            <input type="hidden" name="reviewable_id" id="review-id-input">

            {{-- 星星評分 --}}
            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-600 mb-2">評分</label>
                <div class="flex gap-2" id="star-group">
                    @for($i = 1; $i <= 5; $i++)
                        <button type="button" data-star="{{ $i }}"
                            onclick="setRating({{ $i }})"
                            class="star-btn text-3xl text-gray-200 hover:text-amber-400 transition">★</button>
                    @endfor
                </div>
                <input type="hidden" name="rating" id="rating-input" value="">
            </div>

            {{-- 文字評論 --}}
            <div class="mb-5">
                <label class="block text-xs font-bold text-gray-600 mb-1">評論（選填）</label>
                <textarea name="comment" rows="3" maxlength="500"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-purple-500 focus:ring-purple-500"
                    placeholder="分享您的購物體驗..."></textarea>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeReviewModal()"
                    class="rounded-lg bg-gray-100 px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-200 transition">
                    取消
                </button>
                <button type="submit" id="review-submit-btn"
                    class="rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-white hover:bg-amber-600 transition disabled:opacity-50"
                    disabled>
                    送出評價
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openReviewModal(type, id, reviewee, source) {
    document.getElementById('review-type-input').value = type;
    document.getElementById('review-id-input').value = id;
    document.getElementById('review-modal-reviewee').textContent = reviewee;
    document.getElementById('review-modal-source').textContent = source;
    // 重置
    setRating(0);
    document.querySelector('#review-form textarea[name=comment]').value = '';
    document.getElementById('review-modal').classList.remove('hidden');
    document.getElementById('review-modal').classList.add('flex');
}

function closeReviewModal() {
    document.getElementById('review-modal').classList.add('hidden');
    document.getElementById('review-modal').classList.remove('flex');
}

function setRating(value) {
    document.getElementById('rating-input').value = value;
    document.querySelectorAll('.star-btn').forEach(btn => {
        btn.classList.toggle('text-amber-400', parseInt(btn.dataset.star) <= value);
        btn.classList.toggle('text-gray-200', parseInt(btn.dataset.star) > value);
    });
    document.getElementById('review-submit-btn').disabled = value === 0;
}
</script>