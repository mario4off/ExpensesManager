<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;

/**
 * A euro amount typed in Spanish format (1.234,50), reformatted on blur and
 * stored as a decimal string (1234.50). Shared by every money field (invoice
 * amount, policy premium, ...). No JS mask; negatives are allowed.
 */
class MoneyInput extends TextInput
{
    /** "1.234,50" → "1234.50" */
    private static function toDecimal(?string $state): ?string
    {
        return $state === null ? null : str_replace(',', '.', str_replace('.', '', $state));
    }

    /** "1234.50" → "1.234,50" */
    private static function toSpanish(?string $state): ?string
    {
        return is_numeric($state) ? number_format((float) $state, 2, ',', '.') : $state;
    }

    public function mutateStateForValidation(mixed $state): mixed
    {
        return parent::mutateStateForValidation(self::toDecimal($state));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->rule('numeric')
            ->inputMode('decimal')
            ->suffix('€')
            ->extraInputAttributes(['style' => 'text-align: right'], merge: true)
            ->formatStateUsing(fn ($state) => self::toSpanish($state))
            ->dehydrateStateUsing(fn ($state) => self::toDecimal($state))
            ->live(onBlur: true)
            ->afterStateUpdated(fn (TextInput $component, $state) => $component->state(self::toSpanish(self::toDecimal($state))));
    }
}
