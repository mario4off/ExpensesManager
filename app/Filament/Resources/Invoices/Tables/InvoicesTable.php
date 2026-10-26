<?php

namespace App\Filament\Resources\Invoices\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->label('Número')->searchable(),
                TextColumn::make('client_name')->label('Cliente')->searchable(),
                TextColumn::make('issued_on')->label('Fecha de emisión')->date(),
                TextColumn::make('amount')->label('Importe'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
