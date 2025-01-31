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
        //
    }
    public function creating(Document $document): void
    {
        $uuid = Uuid::uuid4()->toString();
        $microseconds = substr(explode('.', microtime(true))[1], 0, 6);
        $uuid = 'document-' . substr($uuid, 0, 12) . '-' . $microseconds;
        $document->status = 'Active';
        $document->holder = Auth::user()->id;
        $document->id = $uuid;
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
