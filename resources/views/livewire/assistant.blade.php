<div
    wire:ignore.self
    x-data="{
        draft: '',
        pendingMessage: '',
        busy: false,
        async submitMessage() {
            if (this.busy || !this.draft.trim()) return;

            this.pendingMessage = this.draft.trim();
            this.draft = '';
            this.busy = true;
            this.$nextTick(() => this.$refs.history.scrollTop = this.$refs.history.scrollHeight);

            try {
                $wire.$set('message', this.pendingMessage, false);
                await $wire.send();
                if ($wire.error) {
                    if (!this.draft) this.draft = this.pendingMessage;
                } else {
                    this.pendingMessage = '';
                }
            } catch (error) {
                if (!this.draft) this.draft = this.pendingMessage;
            } finally {
                this.busy = false;
                this.$nextTick(() => this.$refs.history.scrollTop = this.$refs.history.scrollHeight);
            }
        },
        resize() {
            const bottomSpacing = parseFloat(getComputedStyle($el.closest('main')).paddingBottom) || 24;
            $el.style.height = Math.max(0, window.innerHeight - $el.getBoundingClientRect().top - bottomSpacing) + 'px';
        },
    }"
    x-init="$nextTick(() => resize())"
    x-on:resize.window="resize()"
    style="display: flex; flex-direction: column; min-height: 0; gap: 1rem;"
>
    <x-filament::section x-ref="history" style="flex: 1; min-height: 0; overflow-y: auto; overscroll-behavior: contain;">
        <div style="display: flex; flex-direction: column; gap: 0.75rem; overflow-wrap: anywhere;">
            @forelse ($messages as $item)
                @if ($item['role'] === 'user')
                    <div style="align-self: flex-end; max-width: 80%; padding: 0.5rem 0.75rem; border-radius: 0.75rem; white-space: pre-wrap; background: color-mix(in oklab, var(--primary-500) 15%, transparent);">{{ $item['content'] }}</div>
                @else
                    <div class="fi-prose" style="align-self: flex-start; max-width: 80%; padding: 0.5rem 0.75rem; border-radius: 0.75rem; background: color-mix(in oklab, currentColor 8%, transparent);">
                        {!! \Illuminate\Support\Str::markdown($item['content'], ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                    </div>
                @endif
            @empty
                <p x-show="!pendingMessage" style="opacity: 0.6;">Escribe tu primera pregunta para empezar la conversación.</p>
            @endforelse

            <div x-cloak x-show="pendingMessage" x-text="pendingMessage" style="align-self: flex-end; max-width: 80%; padding: 0.5rem 0.75rem; border-radius: 0.75rem; white-space: pre-wrap; background: color-mix(in oklab, var(--primary-500) 15%, transparent);"></div>
            <p x-cloak x-show="busy" role="status" style="opacity: 0.6;">Pensando…</p>

            @if ($error)
                <div style="align-self: flex-start; max-width: 80%; padding: 0.5rem 0.75rem; border-radius: 0.75rem; color: var(--danger-600); background: color-mix(in oklab, var(--danger-500) 12%, transparent);">{{ $error }}</div>
            @endif

        </div>
    </x-filament::section>

    <form x-on:submit.prevent="submitMessage()" style="flex-shrink: 0; display: flex; margin-bottom: 40px;  flex-direction: column; gap: 0.5rem;">
        <x-filament::input.wrapper :valid="! $errors->has('message')">
            <x-filament::input
                type="text"
                x-model="draft"
                required
                maxlength="2000"
                placeholder="Escribe tu mensaje…"
                autocomplete="off"
            />
        </x-filament::input.wrapper>

        @error('message')
            <p style="color: var(--danger-600); font-size: 0.875rem;">{{ $message }}</p>
        @enderror

  
      
<div style="display: flex; justify-content: space-between;  margin-top: 10px; ; width: 100%;">    
    @if (count($messages) > 0)
                <x-filament::button
                    color="gray"
                    wire:click="resetConversation"
                    x-on:click="draft = ''; pendingMessage = ''"
                    x-bind:disabled="busy"
                >
                    Nueva conversación
                </x-filament::button>
            @endif
                  <x-filament::button type="submit" x-bind:disabled="busy">
                Enviar
            </x-filament::button></div>
   

    </form>
</div>
