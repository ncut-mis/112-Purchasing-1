<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchasingRequest extends Model
{
    protected $fillable = [
    'user_id', 'title', 'description', 'budget', 'location', 'status'
];

public function user() {
    return $this->belongsTo(User::class);
}
}
