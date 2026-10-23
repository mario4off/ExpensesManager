<?php

use Illuminate\Support\Facades\Validator;

it('uses Spanish as the application locale', function () {
    expect(config('app.locale'))->toBe('es')
        ->and(app()->getLocale())->toBe('es');
});

it('uses the Madrid timezone', function () {
    expect(config('app.timezone'))->toBe('Europe/Madrid')
        ->and(now()->timezoneName)->toBe('Europe/Madrid');
});

it('returns validation messages in Spanish', function () {
    $message = Validator::make([], ['name' => 'required'])->errors()->first('name');

    expect($message)->toContain('obligatorio')
        ->and($message)->not->toContain('required');
});
