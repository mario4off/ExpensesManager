<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Filament\Forms\Components\MoneyInput;
use App\Models\Document;
use App\Models\Invoice;
use App\Services\DocumentStorage;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

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
                    ->getUploadedFileUsing(function (string $file): ?array {
                        $document = Document::find($file);

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
                    ->deleteUploadedFileUsing(function (string $file): void {
                        $document = Document::findOrFail($file);

                        DB::transaction(function () use ($document) {
                            Invoice::where('document_id', $document->id)->update(['document_id' => null]);

                            app(DocumentStorage::class)->delete($document);
                        });
                    })
                    ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file) => (string) app(DocumentStorage::class)->store($file, 'invoices')->getKey()),
            ]);
    }
}
