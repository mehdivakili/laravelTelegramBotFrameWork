<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }
    public function message(){
        return $this->belongsTo(Message::class,'reply_to_message_id');
    }
}
