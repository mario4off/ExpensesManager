---
name: crear-agents-md
description: "Crea el AGENTS.md de la raíz de un proyecto a partir de la descripción que da el usuario, con 300-1500 palabras y siguiendo la guía de agents.md. Úsala cuando el usuario pida crear o regenerar un AGENTS.md. No la uses para README ni CLAUDE.md."
---

# Crear AGENTS.md

## Objetivo
Generar un `AGENTS.md` en la raíz del proyecto, de **300 a 1500 palabras (nunca más)**, con solo lo que un agente necesita y no puede deducir fácilmente del código. Cuanto más concreto y con menos palabras, mejor.

## Entradas
- Descripción del proyecto dada por el usuario: qué es, stack y versiones, comandos, convenciones, límites.
- Si falta algo imprescindible (stack, comandos de ejecutar/tests/lint), pídelo. No inventes datos.
- Lo no esencial que no se haya facilitado se omite; no se rellena.
- No leas el repo para deducir el contenido: la fuente es la descripción.

## Pasos
1. Extrae de la descripción: propósito y arquitectura, stack, comandos, convenciones, límites y verificación final.
2. Redacta `AGENTS.md` con esta estructura y orden:
   - `# AGENTS.md — <proyecto>`
   - `## Proyecto`: qué es, en 2-3 frases (arquitectura y tecnologías).
   - `## Comandos`: ejecutar, tests, lint/formato; cada uno en backticks.
   - `## Estilo y convenciones`: versión del lenguaje, nombres, idioma del código y de los mensajes.
   - `## Reglas`: límites concretos (qué no tocar, qué no añadir sin preguntar). Si el usuario menciona docs de specs o principios, añade "léelos antes de tocar código".
   - `## Al terminar cualquier tarea`: verificación obligatoria (p. ej. ejecutar los tests).
   - Añade `## Seguridad`, `## Commits y PR` o `## Despliegue` solo si el usuario aportó datos.
3. Sé concreto: imperativos cortos, una idea por viñeta, comandos y rutas exactos. Sin relleno, sin lo obvio, sin repetir lo que ya imponen el linter o el README.
4. Cuenta las palabras con `wc -w AGENTS.md`. Si pasa de 1500, recorta (elimina lo deducible, fusiona viñetas). Si no llega a 300, pide al usuario más datos específicos; nunca rellenes.
5. Escribe el archivo en la raíz. Si ya existe uno, muestra los cambios y confirma antes de sobrescribir.

## Salida
`AGENTS.md` en la raíz y una línea final con el recuento de palabras y los datos que faltaron, si los hay.

## Límites
- No modifica código ni README.
- Las instrucciones del usuario en el chat prevalecen sobre AGENTS.md.
- En monorepos, genera un AGENTS.md por subproyecto solo si el usuario lo pide; el más cercano al archivo editado manda.

## Criterio de finalización
- `wc -w AGENTS.md` está entre 300 y 1500.
- Están las secciones base y no queda ningún placeholder `<...>`.
