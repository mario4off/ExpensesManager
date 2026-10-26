<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;

/**
 * An amount in EUR limited to two decimals (RF-09, RF-13, RF-16). Shared by
 * the invoice amount and the policy premium.
 */
class MoneyInput extends TextInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->numeric()
            ->step(0.01)
            ->inputMode('decimal')
            ->suffix('€')
            ->extraInputAttributes(['style' => 'text-align: right'], merge: true);
    }
}
