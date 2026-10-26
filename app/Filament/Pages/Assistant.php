<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

/**
 * Hosts the Livewire assistant in the panel. Any authenticated user can open it,
 * and it sorts after every area section so it never becomes the entry point.
 */
class Assistant extends Page
{
    protected Width|string|null $maxContentWidth = Width::Full;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $navigationLabel = 'Asistente';

    protected static ?int $navigationSort = 10;

    protected static ?string $title = 'Asistente';

    protected string $view = 'filament.pages.assistant';
}
