<?php

namespace App\Observers;

use App\Models\Document;
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
        $meow=Auth::user()->division;
        $uuid = Uuid::uuid4()->toString();
        $uuid = $meow . '-'. date("Y") . '-' . str_pad($document->id, 6, '0', STR_PAD_LEFT);
        $document->status = 'Active';
        $document->holder = Auth::user()->id;
        $document->rmsid = $uuid;
        $document->save();
    }
    public function creating(Document $document): void
    {
        //
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
