<?php

namespace App\Filament\Resources\InsurancePolicies\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InsurancePoliciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('policy_number')->label('Número de póliza')->searchable(),
                TextColumn::make('holder_name')->label('Tomador')->searchable(),
                TextColumn::make('insurer')->label('Aseguradora')->searchable(),
                TextColumn::make('starts_on')->label('Fecha de inicio')->date(),
                TextColumn::make('ends_on')->label('Fecha de fin')->date(),
                TextColumn::make('premium')->label('Prima')->money('EUR'),
            ]);
    }
}
