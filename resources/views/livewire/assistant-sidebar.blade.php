<div
    wire:ignore.self
    x-data="assistantSidebar"
    x-on:toggle-assistant.window="open = !open"
    x-on:keydown.escape.window="open = false"
    x-show="open"
    x-cloak
    x-transition.opacity.duration.150ms
    role="dialog"
    aria-label="Asistente"
    style="position: fixed; top: 0; bottom: 0; right: 0; z-index: 40; width: min(420px, 100vw); background: #fff; border-left: 1px solid var(--gray-200); box-shadow: -8px 0 24px rgb(0 0 0 / 0.08);"
>
    {{-- Inner wrapper keeps the flex layout apart from x-show/x-transition, which rewrite the outer display. --}}
    <div style="height: 100%; display: flex; flex-direction: column;">
    <div style="flex-shrink: 0; display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 1rem; border-bottom: 1px solid var(--gray-200);">
        <span style="font-weight: 600;">Asistente</span>

        <x-filament::icon-button
            icon="heroicon-o-x-mark"
            color="gray"
            label="Cerrar el asistente"
            x-on:click="open = false"
        />
    </div>

    <div x-ref="history" style="flex: 1; min-height: 0; overflow-y: auto; overscroll-behavior: contain; padding: 1rem;">
        <div style="display: flex; flex-direction: column; gap: 0.75rem; overflow-wrap: anywhere;">
            @forelse ($messages as $item)
                @if ($item['role'] === 'user')
                    <div style="align-self: flex-end; font-size: 0.9rem; max-width: 85%; padding: 0.5rem 0.75rem; border-radius: 0.75rem; white-space: pre-wrap; background: color-mix(in oklab, var(--primary-500) 15%, transparent);">{{ $item['content'] }}</div>
                @else
                    <div class="fi-prose" style="align-self: flex-start; max-width: 85%; padding: 0.5rem 0.75rem; border-radius: 0.75rem; background: color-mix(in oklab, currentColor 8%, transparent);">
                        {!! \Illuminate\Support\Str::markdown($item['content'], ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                    </div>
                @endif
            @empty
                <p x-show="!pendingMessage" style="opacity: 0.6;">Escribe tu primera pregunta para empezar la conversación.</p>
            @endforelse

            <div x-cloak x-show="pendingMessage" x-text="pendingMessage" style="align-self: flex-end; max-width: 85%; padding: 0.5rem 0.75rem; border-radius: 0.75rem; white-space: pre-wrap; background: color-mix(in oklab, var(--primary-500) 15%, transparent);"></div>
            <p x-cloak x-show="busy" role="status" style="opacity: 0.6; font-size: 0.9rem;">Pensando…</p>

            @if ($error)
                <div style="align-self: flex-start; max-width: 85%; padding: 0.5rem 0.75rem; border-radius: 0.75rem; color: var(--danger-600); background: color-mix(in oklab, var(--danger-500) 12%, transparent);">{{ $error }}</div>
            @endif
        </div>
    </div>

    <form x-on:submit.prevent="submitMessage()" style="flex-shrink: 0; padding: 0.75rem 1rem 1rem; border-top: 1px solid var(--gray-200);">
        {{-- One card holds the field and both actions, like a chat composer. --}}
        <div style="display: flex; flex-direction: column; gap: 0.5rem; padding: 1rem 1rem 1rem; border: 1px solid var(--gray-300); border-radius: 1rem; background: #fff; box-shadow: 0 1px 2px rgb(0 0 0 / 0.05);">
          <input
            type="text"
            x-ref="input"
            x-model="draft"
            class="focus:outline-none focus:ring-0 focus:shadow-none"
            required
            maxlength="2000"
            placeholder="Pregúntale al asistente…"
            autocomplete="off"
            style="width: 100%; border: 0; outline: none; box-shadow: none; background: transparent; font: inherit; padding: 5px 0px 25px;"
                />

            <div style="display: flex; align-items: center; justify-content: space-between; ">
                @if (count($messages) > 0)
                    <x-filament::icon-button
                        icon="heroicon-o-arrow-path"
                        color="gray"
                         style="border: 1px solid var(--gray-300); border-radius: 100%; margin: 2px"
                        label="Nueva conversación"
                        tooltip="Nueva conversación"
                        wire:click="resetConversation"
                        x-on:click="draft = ''; pendingMessage = ''"
                        x-bind:disabled="busy"
                    />
                @else
                    <span></span>
                @endif

                <x-filament::icon-button
                    type="submit"
                    icon="heroicon-o-arrow-up"
                    style="border: 1px solid var(--gray-300); border-radius: 100%; margin: 2px"
                    color="primary"
                    label="Enviar"
                    tooltip="Enviar"
                    x-bind:disabled="busy"
                />
            </div>
        </div>

        @error('message')
            <p style="color: var(--danger-600); font-size: 0.875rem; margin-top: 0.5rem;">{{ $message }}</p>
        @enderror
    </form>
    </div>
</div>
