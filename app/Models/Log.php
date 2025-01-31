<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'docId',
        'transaction',
        'sender',
        'dateTime',
        'receiver',
        'status',
    ];    
}
