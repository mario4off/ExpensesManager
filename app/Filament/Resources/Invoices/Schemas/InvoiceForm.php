<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Filament\Forms\Components\MoneyInput;
use App\Models\Document;
use App\Models\Invoice;
use App\Services\DocumentStorage;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('number')
                    ->label('Número de factura')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('client_name')
                    ->label('Cliente')
                    ->required()
                    ->maxLength(255),
                DatePicker::make('issued_on')
                    ->label('Fecha de emisión')
                    ->required()
                    ->maxDate(fn () => today()),
                MoneyInput::make('amount')
                    ->label('Importe')
                    ->required(),
                FileUpload::make('document_id')
                    ->label('Documento')
                    ->acceptedFileTypes(['application/pdf', 'image/png'])
                    ->maxSize(10240)
                    ->downloadable()
                    ->openable()
                    ->fetchFileInformation(false)
                    ->getUploadedFileUsing(function (string $file, ?Invoice $record): ?array {
                        // The field state comes from the client: only the record's own document is shown.
                        $document = (string) $record?->document_id === $file ? Document::find($file) : null;

                        if (! $document) {
                            return null;
                        }

                        return [
                            'name' => $document->original_name,
                            'size' => 0,
                            'type' => str_ends_with($document->path, '.pdf') ? 'application/pdf' : 'image/png',
                            'url' => Storage::disk('s3')->temporaryUrl($document->path, now()->addMinutes(30)),
                        ];
                    })
                    ->deleteUploadedFileUsing(function (string $file, ?Invoice $record): void {
                        // The field state comes from the client: only the record's own document can be deleted.
                        abort_unless((string) $record?->document_id === $file, 403);

                        $document = Document::findOrFail($file);

                        DB::transaction(function () use ($document) {
                            Invoice::where('document_id', $document->id)->update(['document_id' => null]);

                            app(DocumentStorage::class)->delete($document);
                        });
                    })
                    ->saveUploadedFileUsing(function (TemporaryUploadedFile $file): string {
                        try {
                            return (string) app(DocumentStorage::class)->store($file, 'invoices')->getKey();
                        } catch (Throwable $exception) {
                            report($exception);

                            Notification::make()
                                ->title('No se pudo subir el documento. No se ha guardado el registro; inténtalo de nuevo.')
                                ->danger()
                                ->send();

                            throw new Halt;
                        }
                    }),
            ]);
    }
}
