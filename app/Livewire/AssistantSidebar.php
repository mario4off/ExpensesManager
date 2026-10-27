<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;

/**
 * The assistant as a slide-over sidebar available on every panel page. It
 * inherits all the logic and the session history from Assistant.
 */
class AssistantSidebar extends Assistant
{
    public function render(): View
    {
        return view('livewire.assistant-sidebar', ['messages' => $this->messages()]);
    }
}
