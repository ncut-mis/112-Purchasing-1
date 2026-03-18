<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PostProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_post_id',
        'name',
        'description',
        'price',
        'currency',
        'max_quantity',
        'sold_quantity',
        'image_path',
        'reference_url',
        'is_active',
    ];

    // 反向關聯：這個商品屬於哪篇代購貼文
    public function post()
    {
        return $this->belongsTo(AgentPost::class, 'agent_post_id');
    }

    public function resolveStoredImagePath(?string $path = null): ?string
    {
        $path = $path ?? $this->image_path;

        if (! $path) {
            return null;
        }

        $normalized = ltrim($path, '/');
        $candidates = array_values(array_unique(array_filter([
            $normalized,
            preg_replace('#^storage/#', '', $normalized),
            preg_replace('#^public/#', '', $normalized),
            preg_replace('#^(storage|public)/#', '', $normalized),
            'agent-post-products/' . basename($normalized),
        ])));

        foreach ($candidates as $candidate) {
            if (Storage::disk('public')->exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    public function getDisplayImageUrlAttribute(): ?string
    {
        return $this->resolveStoredImagePath()
            ? route('post-product.image', $this)
            : null;
    }

    // 關聯：這個商品被加到了哪些購物車 (選配，若之後要統計購物車數據可用)
    // public function carts() ...

    /**
     * 輔助方法：檢查商品是否還能購買
     * 判斷標準：
     * 1. 狀態是 active
     * 2. (如果有設定最大數量) 已售出數量 < 最大數量
     * 
     * @return bool
     */
    public function isBuyable()
    {
        // 先檢查是否上架
        if (!$this->is_active) {
            return false;
        }

        // 如果 max_quantity 是 null，代表無限量，直接回傳 true
        if (is_null($this->max_quantity)) {
            return true;
        }

        // 檢查庫存
        return $this->sold_quantity < $this->max_quantity;
    }

    /**
     * 輔助方法：取得剩餘可代購數量
     * @return int|string
     */
    public function getRemainingStockAttribute()
    {
        if (is_null($this->max_quantity)) {
            return '無限制';
        }
        return $this->max_quantity - $this->sold_quantity;
    }
}