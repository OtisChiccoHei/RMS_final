<?php

namespace App\Observers;

use App\Models\Document;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class DocumentObserver
{
    /**
     * Handle the Document "created" event.
     */
    public function created(Document $document): void
    {
        // Document ID Embedding
        $meow=Auth::user()->division;
        $uuid = Uuid::uuid4()->toString();
        $uuid = $meow . '-'. date("Y") . '-' . str_pad($document->id, 5, '0', STR_PAD_LEFT);
        $document->status = 'Active';
        $document->holder_user = Auth::user()->id;
        $document->holder_division = Auth::user()->division;
        $document->rms_id = $uuid;

        $document->save();

        // Log Creation
        $uuid = Uuid::uuid4()->toString();
        $microseconds = substr(explode('.', microtime(true))[1], 0, 6);
        $uuid = 'log-' . substr($uuid, 0, 12) . '-' . $microseconds;
        $log=Log::create([
            'id' => $uuid,
            'docId' => $document->rmsid,
            'doc_name' => $document->subject,
            'doc_description' => $document->description,
            'doc_type' => $document->docType,
            'user' => Auth::user()->id,
            'user_division' => Auth::user()->division,
            'transaction' => 'Created',
            'receiver' => 'N/A',
            'recipient_division' => 'N/A',
            'actionTaken' => 'N/A',
        ]);
    }
    public function creating(Document $document): void
    {
        
    }
    /**
     * Handle the Document "updated" event.
     */
    public function updated(Document $document): void
    {
        //
    }

    /**
     * Handle the Document "deleted" event.
     */
    public function deleted(Document $document): void
    {
        //
    }

    /**
     * Handle the Document "restored" event.
     */
    public function restored(Document $document): void
    {
        //
    }

    /**
     * Handle the Document "force deleted" event.
     */
    public function forceDeleted(Document $document): void
    {
        //
    }
}
