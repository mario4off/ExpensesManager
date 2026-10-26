<?php

namespace App\Services\Chat;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

/**
 * Talks to the local Ollama server through its /api/chat endpoint, with the
 * whole reply in one response (no streaming) and without the model's reasoning.
 */
class OllamaChatModel implements ChatModel
{
    public function reply(array $messages): string
    {
        try {
            $response = Http::baseUrl(config('services.ollama.url'))
                ->timeout(config('services.ollama.timeout'))
                ->post('/api/chat', [
                    'model' => config('services.ollama.model'),
                    'messages' => $messages,
                    'stream' => false,
                    'think' => false,
                ])
                ->throw();
        } catch (ConnectionException|RequestException $exception) {
            throw new ChatModelUnavailableException('Ollama request failed: '.$exception->getMessage(), previous: $exception);
        }

        $content = $response->json('message.content');

        if (! is_string($content)) {
            throw new ChatModelUnavailableException('Ollama returned no message content.');
        }

        // Some models still reason inside <think> blocks, even closed or cut off.
        $reply = trim(preg_replace('/<think>.*?(<\/think>|$)/s', '', $content));

        if ($reply === '') {
            throw new ChatModelUnavailableException('Ollama returned an empty reply.');
        }

        return $reply;
    }
}
