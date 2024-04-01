<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SentTextMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'text_message',
        'senderid_string',
        'phone_number',
        'status',
        'message_id',
        'response_code',
        'response_description',
        'network_id',
        'delivery_status',
        'delivery_description',
        'delivery_tat',
        'delivery_networkid',
        'delivery_time',
        'delivery_code',
        'delivery_network_id',
        'delivery_response_description',
    ];

}
