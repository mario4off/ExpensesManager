<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;

/**
 * A euro amount limited to two decimals in the browser. Shared by every
 * money field (invoice amount, policy premium, ...). No JS mask and no
 * custom rules on purpose; negatives are allowed.
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
