# Spec 002 — Interfaz del asistente (chat con Ollama)

## Por qué

Fase 1 del chatbot (ver `AGENTS.md`): una interfaz de chat dentro del panel, con memoria de la conversación en la sesión, conectada a un modelo local de Ollama (Qwen o similar). El foco del proyecto es experimentar con el chatbot; esta fase solo deja la conversación funcionando. No consulta datos de la gestoría (fase 2) ni documentos (fase 3).

## Decisiones cerradas

- El asistente es un componente Livewire propio, no un widget de Filament. Se muestra en una barra lateral deslizante a la derecha, integrada en todas las páginas del panel, que se abre y se cierra con un botón de la barra superior.
- Puede usarlo cualquier usuario autenticado del panel; no hay permiso nuevo.
- Modelo de chat: Ollama local, detrás de una interfaz propia (`ChatModel`) para poder cambiar de proveedor sin tocar el componente. Bedrock se mantiene para el RAG de la fase 3.
- Cliente: el facade `Http` de Laravel contra `/api/chat` de Ollama. Sin dependencias nuevas.
- Modelo por defecto: `qwen3:8b`, con `think: false`.
- La conversación vive solo en la sesión de Laravel: sin tablas ni ficheros propios. Se pierde al cerrar sesión.
- Memoria: los últimos 10 mensajes (configurable). El prompt de sistema no se guarda; se antepone en cada envío.
- Respuesta completa con indicador de espera. Sin streaming.
- Sin tests automatizados en esta fase (decisión del usuario); cada tarea se verifica a mano.

## Requisitos funcionales (EARS)

### Acceso y barra lateral

- **RF-01** Cuando un usuario autenticado pulse el botón del asistente en la barra superior de cualquier página del panel, el sistema abrirá una barra lateral a la derecha con la conversación actual y un campo para escribir; cuando lo pulse de nuevo o cierre la barra, el sistema la ocultará.
- **RF-02** Mientras un usuario esté autenticado, el sistema le permitirá usar el asistente sin exigirle ningún permiso de área.

### Conversación

- **RF-03** Cuando el usuario envíe un mensaje, el sistema enviará al modelo el prompt de sistema y los últimos mensajes de la conversación (incluido el nuevo), y mostrará la respuesta del modelo.
- **RF-04** El sistema guardará la conversación solo en la sesión de Laravel y conservará los últimos N mensajes (N configurable, 10 por defecto); al superar el límite descartará los más antiguos.
- **RF-05** Cuando el usuario pulse "Nueva conversación", el sistema vaciará el historial.
- **RF-06** Cuando el usuario cierre sesión, el sistema dejará de conservar su conversación.

### Entrada y estados

- **RF-07** Si el mensaje está vacío, solo contiene espacios o supera los 2000 caracteres, el sistema lo rechazará con un mensaje en español y no llamará al modelo.
- **RF-08** Al enviar, el sistema mostrará inmediatamente el mensaje del usuario y "Pensando…". Mientras espera la respuesta, mantendrá el campo editable y conservará el siguiente borrador; desactivará únicamente el envío y el reinicio de conversación para evitar peticiones simultáneas.
- **RF-09** Si el modelo no responde (caído, tiempo agotado o error), el sistema mostrará un mensaje en español dentro del chat y no guardará ese intercambio en el historial.

### Modelo y contenido

- **RF-10** El sistema usará un modelo local de Ollama, con la URL, el modelo y el tiempo máximo de espera configurados solo en el `.env`.
- **RF-11** El sistema indicará al modelo, con un prompt de sistema en español, que es el asistente de la gestoría, que responde siempre en español y que en esta fase no tiene acceso a facturas, pólizas ni documentos.
- **RF-12** El sistema mostrará las respuestas del modelo como Markdown sin HTML y los mensajes del usuario como texto plano.
- **RF-13** El sistema no mostrará el razonamiento interno del modelo (bloques `<think>`).

## Fuera de alcance

- Consultas SQL sobre gastos y RAG con Bedrock (fases 2 y 3, specs propias).
- Streaming de la respuesta.
- Persistencia del historial en base de datos o en ficheros, y varias conversaciones.
- Permisos o control de acceso específicos del asistente.
- Elegir el modelo desde la interfaz.
- Tests automatizados (por ahora).

## Criterios de finalización

1. Con Ollama instalado, el usuario conversa en el panel y comprueba a mano RF-01 a RF-13.
2. Sin dependencias nuevas ni cambios de esquema de base de datos.
3. Todos los mensajes al usuario están en español; las referencias de código, en inglés.
4. La URL, el modelo y el tiempo máximo de espera salen solo del `.env`; ningún secreto en código ni commits.
5. `./vendor/bin/pint` sin cambios y `./vendor/bin/pest` en verde (la suite de 001).
