<?php

namespace App\Filament\Resources\Invoices\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
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
                TextColumn::make('amount')->label('Importe')->money('EUR'),
            ])
            ->defaultSort('issued_on', 'desc')
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make()
                        ->failureNotificationTitle('No se pudo borrar el documento de S3. El registro no se ha eliminado; inténtalo de nuevo.'),
                ]),
            ]);
    }
}
