<?php

namespace App\Filament\Resources\InsurancePolicies\Pages;

use App\Filament\Concerns\CompensatesDocumentOnCreateFailure;
use App\Filament\Resources\InsurancePolicies\InsurancePolicyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInsurancePolicy extends CreateRecord
{
    use CompensatesDocumentOnCreateFailure;

    protected static string $resource = InsurancePolicyResource::class;
}
