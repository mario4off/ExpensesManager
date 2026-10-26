<?php

namespace App\Services\Chat;

/**
 * The chat provider behind the assistant. The component only knows this
 * interface, so the provider (Ollama now) can change without touching it.
 */
interface ChatModel
{
    /**
     * @param  array<int, array{role: string, content: string}>  $messages  The system prompt followed by the conversation.
     *
     * @throws ChatModelUnavailableException
     */
    public function reply(array $messages): string;
}
