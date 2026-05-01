<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_id',
        'request_item_id',
        'unit_price',
    ];

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function requestItem()
    {
        return $this->belongsTo(RequestItem::class);
    }
}