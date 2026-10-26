<?php

namespace App\Filament\Resources\InsurancePolicies;

use App\Filament\Resources\InsurancePolicies\Pages\CreateInsurancePolicy;
use App\Filament\Resources\InsurancePolicies\Pages\EditInsurancePolicy;
use App\Filament\Resources\InsurancePolicies\Pages\ListInsurancePolicies;
use App\Filament\Resources\InsurancePolicies\Schemas\InsurancePolicyForm;
use App\Filament\Resources\InsurancePolicies\Tables\InsurancePoliciesTable;
use App\Models\InsurancePolicy;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InsurancePolicyResource extends Resource
{
    protected static ?string $model = InsurancePolicy::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'póliza';

    protected static ?string $pluralModelLabel = 'pólizas';

    public static function form(Schema $schema): Schema
    {
        return InsurancePolicyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InsurancePoliciesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInsurancePolicies::route('/'),
            'create' => CreateInsurancePolicy::route('/create'),
            'edit' => EditInsurancePolicy::route('/{record}/edit'),
        ];
    }
}
