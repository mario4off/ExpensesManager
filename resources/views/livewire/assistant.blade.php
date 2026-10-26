<div>
    <x-filament::section>
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            @forelse ($messages as $item)
                @if ($item['role'] === 'user')
                    <div style="align-self: flex-end; max-width: 80%; padding: 0.5rem 0.75rem; border-radius: 0.75rem; white-space: pre-wrap; background: color-mix(in oklab, var(--primary-500) 15%, transparent);">{{ $item['content'] }}</div>
                @else
                    <div class="fi-prose" style="align-self: flex-start; max-width: 80%; padding: 0.5rem 0.75rem; border-radius: 0.75rem; background: color-mix(in oklab, currentColor 8%, transparent);">
                        {!! \Illuminate\Support\Str::markdown($item['content'], ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                    </div>
                @endif
            @empty
                <p style="opacity: 0.6;">Escribe tu primera pregunta para empezar la conversación.</p>
            @endforelse

            @if ($error)
                <div style="align-self: flex-start; max-width: 80%; padding: 0.5rem 0.75rem; border-radius: 0.75rem; color: var(--danger-600); background: color-mix(in oklab, var(--danger-500) 12%, transparent);">{{ $error }}</div>
            @endif

            <p wire:loading wire:target="send" style="opacity: 0.6;">El asistente está escribiendo…</p>
        </div>
    </x-filament::section>

    <form wire:submit="send" style="margin-top: 1rem; display: flex; flex-direction: column; gap: 0.5rem;">
        <x-filament::input.wrapper :valid="! $errors->has('message')">
            <x-filament::input
                type="text"
                wire:model="message"
                wire:loading.attr="disabled"
                wire:target="send"
                placeholder="Escribe tu mensaje…"
                autocomplete="off"
            />
        </x-filament::input.wrapper>

        @error('message')
            <p style="color: var(--danger-600); font-size: 0.875rem;">{{ $message }}</p>
        @enderror

        <div style="display: flex; gap: 0.5rem;">
            <x-filament::button type="submit" wire:loading.attr="disabled" wire:target="send">
                Enviar
            </x-filament::button>

            @if (count($messages) > 0)
                <x-filament::button
                    color="gray"
                    wire:click="resetConversation"
                    wire:loading.attr="disabled"
                    wire:target="send"
                >
                    Nueva conversación
                </x-filament::button>
            @endif
        </div>
    </form>
</div>
