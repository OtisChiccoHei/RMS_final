<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Observers\DocumentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([DocumentObserver::class])]

class Document extends Model
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
     public $incrementing = true;
    
    protected $fillable = [
        'id',
        'rms_id',
        'subject',
        'status',
        'docType',
        'initialDraft',
        'finalDraft',
        'signedCopy',
        'holder_user',
        'holder_division',
        'description',
        'forward_id',
        'actionTaken',
    ];
    
    protected $casts = [
        'actionTaken' => 'json',
    ];
}
