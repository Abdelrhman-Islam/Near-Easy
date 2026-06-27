<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    /** @use HasFactory<\Database\Factories\SubscriptionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'status',
        'is_free_tier',
        'start_date',
        'expire_date',
        'is_active',
    ];

    public function user() 
    { 
        return $this->belongsTo(User::class); 
    }
    public function plan() 
    { 
        return $this->belongsTo(Plan::class); 
    }
    //A single subscription may have multiple payment requests (for example, if the first one is rejected).
    public function paymentRequests()
    {
        return $this->hasMany(PaymentRequest::class);
    }

    // Retrieve the latest payment request for this subscription - "admin control panel"
    public function latestPaymentRequest()
    {
        return $this->hasOne(PaymentRequest::class)->latestOfMany();
    }
}
