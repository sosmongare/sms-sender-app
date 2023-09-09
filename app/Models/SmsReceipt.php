<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'mobilenumber','response_code',
        'response_description',
        'message_id','delivery_status',
        'delivery_description','delivery_tat',
        'delivery_networkid','delivery_time',
    ];
}
