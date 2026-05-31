{{-- 跟單 Modal --}}
<div class="modal fade follow-order-modal" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 bg-light py-3 px-4">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-cart-plus me-2 text-primary"></i>確認跟團商品</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('orders.store', $agentPost) }}" method="POST" class="follow-order-form">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 border rounded-4 h-100 bg-light-subtle">
                                <label class="text-muted small fw-bold text-uppercase d-block mb-1">預計代購時段</label>
                                <div class="text-dark fw-bold">
                                    {{ optional($agentPost->start_date)->format('Y/m/d') }} <span class="mx-1 text-muted">至</span> {{ optional($agentPost->end_date)->format('Y/m/d') }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-4 h-100 bg-light-subtle">
                                <label class="text-muted small fw-bold text-uppercase d-block mb-1">描述訊息</label>
                                <div class="text-muted small text-truncate">
                                    {{ $agentPost->description ?: '無詳細說明。' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="bg-light">
                                <tr class="small text-muted border-0">
                                    <th class="border-0 ps-0" style="width: 70px;">圖片</th>
                                    <th class="border-0">商品名稱</th>
                                    <th class="border-0 text-center">可下單數量</th>
                                    <th class="border-0 text-center">單價</th>
                                    <th class="border-0 text-center">數量</th>
                                    <th class="border-0 text-end pe-0">小計</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($agentPost->products as $product)
                                    @php
                                        $max = $product->max_quantity ?? 0;
                                        $sold = $product->sold_quantity ?? 0;
                                        $remaining = $max - $sold;
                                    @endphp
                                    <tr class="product-row" data-price="{{ $product->price }}">
                                        <td class="ps-0">
                                            <img src="{{ $product->display_image_url ?? 'https://via.placeholder.com/60' }}"
                                                 class="rounded-3 object-fit-cover shadow-sm" width="55" height="55">
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark mb-0">{{ $product->name }}</div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $remaining > 0 ? 'bg-info-subtle text-info' : 'bg-danger-subtle text-danger' }} rounded-pill">
                                                {{ $remaining > 0 ? '還有 ' . $remaining : '已售罄' }}
                                            </span>
                                        </td>
                                        <td class="text-center fw-semibold text-muted">
                                            ${{ number_format($product->price) }}
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm border rounded-pill overflow-hidden bg-white mx-auto" style="max-width: 110px;">
                                                <button class="btn btn-link text-decoration-none border-0 px-2 qty-minus" type="button" {{ $remaining <= 0 ? 'disabled' : '' }}>
                                                    <i class="bi bi-dash-lg"></i>
                                                </button>
                                                <input type="number" name="products[{{ $product->id }}][quantity]"
                                                       class="form-control border-0 text-center bg-transparent qty-input"
                                                       value="0"
                                                       min="0"
                                                       max="{{ $remaining }}"
                                                       {{ $remaining <= 0 ? 'disabled' : '' }}
                                                       style="box-shadow: none;">
                                                <button class="btn btn-link text-decoration-none border-0 px-2 qty-plus" type="button" {{ $remaining <= 0 ? 'disabled' : '' }}>
                                                    <i class="bi bi-plus-lg"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="text-end pe-0 fw-bold text-primary subtotal">
                                            $0
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer border-0 p-4 pt-0 flex-column align-items-end">
                    <div class="d-flex align-items-baseline mb-4">
                        <span class="text-muted me-3">總計金額：</span>
                        <span class="h3 fw-bold text-success mb-0">NT$ <span class="total-amount">0</span></span>
                    </div>
                    <div class="d-flex gap-2 w-100">
                        <button type="button" class="btn btn-light rounded-pill flex-grow-1 py-2 fw-bold" data-bs-dismiss="modal">再逛逛</button>
                        <button type="submit" class="btn btn-primary-custom rounded-pill flex-grow-1 py-2 fw-bold shadow follow-order-submit-btn">確認結帳</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
