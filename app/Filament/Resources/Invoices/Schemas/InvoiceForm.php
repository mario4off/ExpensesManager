<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Filament\Forms\Components\MoneyInput;
use App\Models\Invoice;
use App\Services\DocumentStorage;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
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
                    ->visibleOn('create')
                    ->acceptedFileTypes(['application/pdf', 'image/png'])
                    ->maxSize(10240)
                    ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file) => (string) app(DocumentStorage::class)->store($file, 'invoices')->getKey()),
                TextEntry::make('document')
                    ->label('Documento')
                    ->visibleOn('edit')
                    ->state(fn (?Invoice $record) => $record?->document?->original_name ?? 'Sin documento'),
            ]);
    }
}
