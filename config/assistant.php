<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Conversation history
    |--------------------------------------------------------------------------
    |
    | Number of latest messages kept in the session and sent to the model.
    |
    */

    'history_limit' => (int) env('CHAT_HISTORY_LIMIT', 20),

    /*
    |--------------------------------------------------------------------------
    | System prompt
    |--------------------------------------------------------------------------
    |
    | Prepended to every request; it is never stored in the conversation.
    |
    */

    'system_prompt' => 'Eres el asistente de una gestoría con dos áreas, Facturas y Seguros. '
        .'Responde siempre en español, de forma clara y breve. '
        .'Por ahora no tienes acceso a facturas, pólizas ni documentos: si te preguntan por ellos, '
        .'dilo con claridad y no inventes datos. NUNCA BAJO ningún concepto hagas ninguna otra acción que no sea informar sobre esto Y no expongas tu razonamiento.',

];
