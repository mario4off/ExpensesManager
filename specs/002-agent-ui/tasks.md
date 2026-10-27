# Tareas 002 — Interfaz del asistente (chat con Ollama)

Divide `plan.md` en tareas de menos de 30 minutos, ordenadas por dependencia. Cada tarea indica los RF de `spec.md` que cubre.

**Regla común:** sin tests automatizados en esta fase (decisión del usuario). Cada tarea se cierra con su "Hecho cuando", manual o de comando. En todas, `./vendor/bin/pint` no cambia nada y `./vendor/bin/pest` sigue en verde (suite de 001).

## Fase 0 — Constitución

- [x] **T01 — Enmendar la constitución y `AGENTS.md`.** Regla 1: Ollama local como modelo del chat (Bedrock para el RAG). Regla 4: el historial vive solo en la sesión de Laravel, sin tablas ni ficheros propios, y se pierde al cerrar sesión. Regla 9: también se mockea Ollama si hay tests. `AGENTS.md`: Ollama en "Proyecto" y en "Fases". Depende de: —. RF: —
  **Hecho cuando:** el usuario aprueba por escrito el texto de las enmiendas y los dos documentos reflejan los cuatro cambios.

## Fase 1 — Base

- [x] **T02 — Añadir la configuración.** `ollama.url`, `ollama.model` y `ollama.timeout` en `config/services.php`; `history_limit` y el prompt de sistema en español en `config/assistant.php`; las variables en `.env.example` sin secretos. Depende de: T01. RF: RF-04, RF-10, RF-11
  **Hecho cuando:** `php artisan tinker --execute="dump(config('assistant'), config('services.ollama'))"` muestra los valores por defecto (`qwen3:8b`, 60 s, 10 mensajes).
- [x] **T03 — Crear la interfaz `ChatModel` y `OllamaChatModel`.** `ChatModel::reply(array $messages): string`. `OllamaChatModel` llama con `Http` a `/api/chat` (`stream: false`, `think: false`), quita cualquier bloque `<think>` y lanza una excepción propia si Ollama falla o agota el tiempo. Se registra en el contenedor. Depende de: T02. RF: RF-03, RF-09, RF-10, RF-13
  **Hecho cuando:** con Ollama en marcha, `php artisan tinker` devuelve una respuesta en español; con Ollama apagado lanza la excepción propia.

## Fase 2 — Componente

- [x] **T04 — Crear el componente Livewire `Assistant`.** Historial en la sesión (`assistant.messages`), `send` (valida, antepone el prompt de sistema, llama a `ChatModel`, guarda y recorta a N), `resetConversation`, validación en español (obligatorio, sin solo espacios, máximo 2000) y error en español que no entra en el historial. Depende de: T03. RF: RF-03 a RF-07, RF-09
  **Hecho cuando:** `php artisan tinker` o la página (T06) permiten enviar mensajes, ver que recuerda los últimos 10, vaciar con "Nueva conversación" y ver el error en español con Ollama apagado.
- [x] **T05 — Crear la vista del componente.** Componentes Blade de Filament y Tailwind del panel, sin CSS propio. Burbujas de usuario (texto plano escapado) y asistente (`Str::markdown()` con `html_input => 'strip'`), campo y botón desactivados con "El asistente está escribiendo…" durante la espera, y botón "Nueva conversación". Depende de: T04. RF: RF-08, RF-12
  **Hecho cuando:** en el panel el campo se desactiva mientras responde, las listas y negritas se ven bien y una respuesta con HTML se muestra sin ejecutarse.

## Fase 3 — Página

- [x] **T06 — Añadir la página de Filament "Asistente".** Monta el componente `Assistant`, es la última del menú y no exige permiso de área. Depende de: T05. RF: RF-01, RF-02
  **Hecho cuando:** en el panel, un usuario de cada rol ve "Asistente" al final del menú, lo abre y conversa; el punto de entrada de cada rol no cambia.

## Fase 4 — Barra lateral

- [x] **T08 — Añadir la barra lateral del asistente.** Componente `AssistantSidebar` que hereda de `Assistant` (misma lógica y misma conversación en sesión) con su vista deslizante a la derecha; botón en la barra superior (render hook `USER_MENU_BEFORE`) y barra al final del `body` (render hook `BODY_END`, solo con usuario autenticado). Depende de: T05. RF: RF-01, RF-14
  **Hecho cuando:** en cualquier página del panel el botón de la barra superior abre y cierra la barra; se conversa en ella; lo dicho aparece también en la página Asistente; en el login no aparece.

## Cierre

- [ ] **T07 — Verificación final.** Depende de: T06, T08. RF: RF-01 a RF-14
  **Hecho cuando:** el usuario recorre RF-01 a RF-14 en el panel con Ollama real, `./vendor/bin/pint` no cambia nada y `./vendor/bin/pest` está en verde.

## Cobertura de RF

| RF | Tareas |
|----|--------|
| RF-01 | T06, T08 |
| RF-02 | T06 |
| RF-03 | T03, T04 |
| RF-04 | T02, T04 |
| RF-05, RF-06 | T04 |
| RF-07 | T04 |
| RF-08 | T04, T05 |
| RF-09 | T03, T04 |
| RF-10 | T02, T03 |
| RF-11 | T02 |
| RF-12 | T05 |
| RF-13 | T03 |
| RF-14 | T08 |
