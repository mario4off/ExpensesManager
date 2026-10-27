<?php

namespace App\Livewire;

use App\Services\Chat\ChatModel;
use App\Services\Chat\ChatModelUnavailableException;
use Livewire\Component;

abstract class Assistant extends Component
{
    private const SESSION_KEY = 'assistant.messages';

    public string $message = '';

    public ?string $error = null;

    public function send(ChatModel $chat): void
    {
        $this->error = null;

        $this->validate(
            ['message' => ['required', 'string', 'max:2000']],
            [
                'message.required' => 'Escribe un mensaje.',
                'message.max' => 'El mensaje no puede superar los :max caracteres.',
            ],
        );

        $history = [...$this->messages(), ['role' => 'user', 'content' => trim($this->message)]];
        $history = $this->latest($history);

        try {
            $reply = $chat->reply([
                ['role' => 'system', 'content' => config('assistant.system_prompt')],
                ...$history,
            ]);
        } catch (ChatModelUnavailableException $exception) {
            report($exception);

            // The failed exchange is not stored and the text stays in the field to retry.
            $this->error = 'El asistente no está disponible ahora mismo. Inténtalo de nuevo en unos minutos.';

            return;
        }

        session()->put(self::SESSION_KEY, $this->latest([...$history, ['role' => 'assistant', 'content' => $reply]]));

        $this->message = '';
    }

    public function resetConversation(): void
    {
        session()->forget(self::SESSION_KEY);

        $this->message = '';
        $this->error = null;
        $this->resetValidation();
    }

    /**
     * The history is read from the session on every render and never held in a
     * public property, so the client cannot tamper with what is sent to the model.
     *
     * @return array<int, array{role: string, content: string}>
     */
    public function messages(): array
    {
        return session(self::SESSION_KEY, []);
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array<int, array{role: string, content: string}>
     */
    private function latest(array $messages): array
    {
        return array_slice($messages, -config('assistant.history_limit'));
    }
}
