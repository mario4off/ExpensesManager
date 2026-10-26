<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Concerns\CompensatesDocumentOnCreateFailure;
use App\Filament\Resources\Invoices\InvoiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    use CompensatesDocumentOnCreateFailure;

    protected static string $resource = InvoiceResource::class;
}
