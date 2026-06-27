<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentRequest extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentRequestFactory> */
    use HasFactory;

    protected $fillable = [
        "subscription_id",
        "payment_method",
        "screenshot_path",
        "status",
        "admin_notes",
    ];

    //Linking the request to its associated subscription
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
