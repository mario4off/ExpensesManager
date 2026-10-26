<?php

namespace App\Filament\Concerns;

use App\Services\DocumentStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * The upload field stores the file in s3 before the record is created, so if
 * creating the record fails the file and its document row are removed again.
 */
trait CompensatesDocumentOnCreateFailure
{
    protected function handleRecordCreation(array $data): Model
    {
        try {
            return DB::transaction(fn () => parent::handleRecordCreation($data));
        } catch (Throwable $exception) {
            app(DocumentStorage::class)->discard($data['document_id'] ?? null);

            throw $exception;
        }
    }
}
