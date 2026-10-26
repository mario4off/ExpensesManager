<?php

namespace App\Services\Chat;

use RuntimeException;

/**
 * Raised when the chat model is down, times out, fails or returns nothing.
 */
class ChatModelUnavailableException extends RuntimeException {}
