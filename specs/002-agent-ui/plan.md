# Plan 002 — Interfaz del asistente (chat con Ollama)

Implementa `spec.md` (RF-01 a RF-13). Respeta `docs/constitution.md`, con las enmiendas del primer punto.

## Puntos que requieren aprobación (reglas 1, 4 y 12)

1. **Stack (regla 1):** se añade Ollama local como modelo del chat provisional. Bedrock sigue siendo el proveedor del RAG.
2. **Historial en sesión (regla 4):** la regla pasa a decir que el historial vive solo en la sesión de Laravel, sin tablas ni ficheros propios, y que se pierde al cerrar sesión. Se mantiene `SESSION_DRIVER=database`; la sesión de Laravel ya usa la tabla `sessions` y esta fase no crea ninguna tabla.
3. **Mocks (regla 9):** si algún día hay tests, también mockean Ollama.
4. **Sin dependencias ni esquema:** el cliente usa `Http` de Laravel y no hay migraciones.

## Módulos

| # | Módulo | Contenido | RF |
|---|--------|-----------|----|
| 0 | Constitución | Enmiendas a `docs/constitution.md` y `AGENTS.md` | — |
| 1 | Configuración | `config/services.php` (`ollama`), `config/assistant.php`, `.env.example` | RF-04, RF-10, RF-11 |
| 2 | Cliente del modelo | Interfaz `ChatModel` y `OllamaChatModel` (`Http`, `think: false`, limpieza de `<think>`) | RF-03, RF-09, RF-10, RF-13 |
| 3 | Componente | `Assistant` Livewire: historial en sesión, envío, validación, errores, "Nueva conversación" | RF-03 a RF-09 |
| 4 | Vista | Blade del componente con componentes de Filament; Markdown seguro; estado de espera | RF-08, RF-12 |
| 5 | Página | Página de Filament "Asistente", última del menú | RF-01, RF-02 |

## Datos

Sin tablas nuevas. La conversación es una lista en la sesión, clave `assistant.messages`:

```
[['role' => 'user'|'assistant', 'content' => '...'], ...]
```

El prompt de sistema no se guarda: se antepone en cada envío.

## Decisiones justificadas

| Decisión | Por qué | Alternativa descartada |
|----------|---------|------------------------|
| Interfaz `ChatModel` con una implementación Ollama | El componente no conoce el proveedor; las fases 2 y 3 pueden cambiarlo | Llamar a Ollama desde el componente: acopla UI y proveedor |
| `Http` de Laravel contra `/api/chat` | Sin dependencias nuevas (regla 12); la API es una petición JSON | Paquetes de LLM (Prism, ollama-laravel): dependencia nueva sin necesidad |
| Historial en la sesión de Laravel | Regla 4: conversación efímera; encaja con "sin persistencia en base de datos" | Tabla de mensajes: contradice la regla 4. Cookie: límite de unos 4 KB |
| Últimos 10 mensajes, `CHAT_HISTORY_LIMIT` | Acota el contexto y el tamaño de la sesión | Historial completo: crece sin límite |
| Respuesta completa, sin streaming | Más simple; el streaming puede ser una tarea posterior | `stream()` de Livewire: más complejidad en esta fase |
| `think: false` y limpieza de `<think>` | Qwen3 razona en voz alta: ensucia el chat y alarga la espera | Mostrar el razonamiento: ruido para el usuario |
| `Str::markdown()` con `html_input => 'strip'` | Qwen responde en Markdown; el HTML del modelo no debe llegar al navegador | Texto plano: pierde listas y negritas. HTML sin filtrar: inyección |
| Página de Filament que monta el componente | Login, layout y navegación sin controladores (reglas 2 y AGENTS.md) | Ruta propia fuera del panel: duplica autenticación |
| "Asistente" al final del menú | No cambia el punto de entrada (regla 15) | Primero del menú: se convertiría en la entrada de todos |

## Riesgos conocidos

- **Ollama no está instalado en la máquina de desarrollo.** Hay que instalarlo y descargar el modelo; sin eso, la conversación real no se puede probar.
- **Latencia y memoria:** `qwen3:8b` necesita varios GB de RAM y puede tardar. Si va justo, usar `qwen3:4b` en `.env`.
- **Tamaño de la sesión:** con `SESSION_DRIVER=database`, el historial se guarda en el campo `payload` de `sessions`. Con 10 mensajes de hasta 2000 caracteres cabe sin problema.
- **Respuestas del modelo largas o con Markdown inesperado:** el render las escapa y no admite HTML.

## Estrategia de verificación

Sin tests automatizados en esta fase (decisión del usuario). Cada tarea se cierra con su "Hecho cuando" manual o de comando. En todas, `./vendor/bin/pint` no cambia nada y `./vendor/bin/pest` sigue en verde (suite de 001).

## Puesta en marcha de Ollama

1. Instalar: `brew install ollama`.
2. Arrancar el servidor: `ollama serve`.
3. Descargar el modelo: `ollama pull qwen3:8b`.
4. Variables en `.env`: `OLLAMA_URL=http://localhost:11434`, `OLLAMA_MODEL=qwen3:8b`, `OLLAMA_TIMEOUT=60`, `CHAT_HISTORY_LIMIT=10`.

## Cobertura de la spec

| RF | Módulos |
|----|---------|
| RF-01, RF-02 | 5 |
| RF-03 | 2, 3 |
| RF-04 | 1, 3 |
| RF-05, RF-06 | 3 |
| RF-07 | 3 |
| RF-08 | 3, 4 |
| RF-09 | 2, 3 |
| RF-10 | 1, 2 |
| RF-11 | 1 |
| RF-12 | 4 |
| RF-13 | 2 |
