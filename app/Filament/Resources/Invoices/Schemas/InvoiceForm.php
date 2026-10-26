<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Filament\Forms\Components\MoneyInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

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
            ]);
    }
}
