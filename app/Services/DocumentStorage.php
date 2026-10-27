<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * The only place that talks to the private s3 disk for documents. The s3 disk
 * is configured with "throw", so a failure raises an exception: the file goes
 * first and the row second on store, the file first and the row second on delete.
 */
class DocumentStorage
{
    /** Root folder of every document in the bucket. */
    private const ROOT = 'docs';

    /**
     * Upload the file to s3 as docs/{area}/{uuid}.{ext} and create its document
     * row, keeping the area in the database and the path.
     */
    public function store(UploadedFile $file, string $area): Document
    {
        $extension = $file->guessExtension() ?? $file->getClientOriginalExtension();
        $path = self::ROOT.'/'.$area.'/'.Str::uuid().'.'.$extension;

        $stream = fopen($file->getRealPath(), 'r');

        try {
            Storage::disk('s3')->put($path, $stream);
        } finally {
            fclose($stream);
        }

        return Document::create([
            'area' => $area,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
        ]);
    }

    /**
     * Delete the file from s3 and then the row; if s3 fails the row is kept.
     */
    public function delete(Document $document): void
    {
        Storage::disk('s3')->delete($document->path);

        $document->delete();
    }

    /**
     * Delete the document with the given id, if it exists. Used to undo an upload
     * whose record could not be saved.
     */
    public function discard(int|string|null $documentId): void
    {
        $document = Document::find($documentId);

        if ($document) {
            $this->delete($document);
        }
    }

    /**
     * @return resource|null
     */
    public function readStream(Document $document)
    {
        return Storage::disk('s3')->readStream($document->path);
    }
}
