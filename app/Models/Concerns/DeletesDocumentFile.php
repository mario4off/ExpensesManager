<?php

namespace App\Models\Concerns;

use App\Services\DocumentStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Deletes the document file from s3 when the record is deleted. The reference is
 * cleared first because document_id has no ON DELETE action, and the clear is
 * rolled back if s3 fails, so the record keeps its document.
 */
trait DeletesDocumentFile
{
    protected static function bootDeletesDocumentFile(): void
    {
        static::deleting(function (Model $model) {
            $documentId = $model->document_id;

            if ($documentId === null) {
                return;
            }

            try {
                DB::transaction(function () use ($model, $documentId) {
                    $model->newQuery()->whereKey($model->getKey())->update(['document_id' => null]);

                    app(DocumentStorage::class)->discard($documentId);
                });
            } catch (Throwable $exception) {
                report($exception);

                // Returning false cancels the delete, so the record stays with its document.
                return false;
            }
        });
    }
}
