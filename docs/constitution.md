# Constitución — ExpensesManager
1. Stack fijo: Laravel + Filament + Livewire + Pest; MySQL (base de datos), S3 (documentos), Ollama local (modelo del chat, provisional para pruebas e intercambiable por OpenAI) y Bedrock (RAG). Nada fuera sin aprobación.
2. Filament primero: todo el CRUD con recursos de Filament; solo el asistente es Livewire propio.
3. Fases en orden: conversación en sesión → SQL → RAG. Una fase por spec; no se adelanta la siguiente.
4. Conversación efímera: el historial vive solo en la sesión de Laravel, sin tablas ni ficheros propios, y se pierde al cerrar sesión.
5. SQL seguro: el asistente solo ejecuta consultas de solo lectura, acotadas al usuario autenticado.
6. Documentos en S3: ningún documento en disco local.
7. Secretos en `.env`: nunca en código, tests ni commits.
8. Test primero en lo crítico: autorización (rol `admin` y permisos de área), validaciones de negocio y documentos en S3 llevan un test Pest escrito antes y verificado en rojo. Configuración, apariencia y comportamiento estándar de Laravel o Filament no llevan test; se verifican a mano.
9. Servicios externos mockeados: los tests nunca llaman a S3, Bedrock, Ollama ni OpenAI reales.
10. Calidad: `./vendor/bin/pint` sin cambios y `./vendor/bin/pest` en verde antes de cada commit.
11. Idioma: referencias (código, nombres de clases, métodos, variables, tablas, claves) en inglés; mensajes al usuario en español.
12. Dependencias y esquema: no se añaden paquetes ni se cambia el esquema de BD sin preguntar.
13. Spec antes de código: cada cambio parte de una spec activa; sin spec, no se toca código.
14. Acceso por área: Spatie Laravel Permission con permisos de área (`invoices`, `insurance_policies`) asignados a roles; cada usuario solo accede a los recursos y documentos de las áreas que permiten sus roles.
15. Punto de entrada: el sistema no tiene dashboard. Cada usuario entra a la primera sección que su rol le permite ver; el `admin` entra siempre a Usuarios. Todo usuario tiene al menos una sección permitida.
