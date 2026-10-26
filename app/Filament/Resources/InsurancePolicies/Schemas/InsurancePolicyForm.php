<?php

namespace App\Filament\Resources\InsurancePolicies\Schemas;

use App\Filament\Forms\Components\MoneyInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InsurancePolicyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('policy_number')
                    ->label('Número de póliza')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('holder_name')
                    ->label('Tomador')
                    ->required()
                    ->maxLength(255),
                TextInput::make('insurer')
                    ->label('Aseguradora')
                    ->required()
                    ->maxLength(255),
                DatePicker::make('starts_on')
                    ->label('Fecha de inicio')
                    ->required(),
                DatePicker::make('ends_on')
                    ->label('Fecha de fin')
                    ->required()
                    ->afterOrEqual('starts_on'),
                MoneyInput::make('premium')
                    ->label('Prima')
                    ->required(),
            ]);
    }
}
