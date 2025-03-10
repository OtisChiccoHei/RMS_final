<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Forward extends Model
{
    use HasFactory;
     /**
     * The primary key associated with the table.
     *
     * @var string
     */

        /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */

    /**
     * The data type of the primary key ID.
     *
     * @var string
     */

     protected $primaryKey = 'id';
     public $incrementing = false;
     protected $keyType = 'string';
     
    protected $fillable = [
        'id',
        'documentId',
        'document_name',
        'document_description',
        'document_type',
        'sender',
        'sender_division',
        'remarks',
        'receiver',
        'receiver_division',
        'receiver_divisionTemp',
        'status',
    ];
}
