<?php

namespace App\Filament\Resources\InsurancePolicies;

use App\Filament\Resources\InsurancePolicies\Pages\ListInsurancePolicies;
use App\Filament\Resources\InsurancePolicies\Tables\InsurancePoliciesTable;
use App\Models\InsurancePolicy;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InsurancePolicyResource extends Resource
{
    protected static ?string $model = InsurancePolicy::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'póliza';

    protected static ?string $pluralModelLabel = 'pólizas';

    public static function table(Table $table): Table
    {
        return InsurancePoliciesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInsurancePolicies::route('/'),
        ];
    }
}
