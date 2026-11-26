# Harvey – Agente de ventas R2CP

Harvey tiene dos partes:

## 1. Harvey copiloto (te ayuda a vender a ti)
Es un skill de Claude Code que está en `.claude/skills/harvey/`. Se carga solo cuando trabajas
en este repositorio. Ejemplos de lo que le puedes pedir:

- "Harvey, califica este lead: [pega el mensaje del prospecto]"
- "Harvey, prepárame la asesoría de 20 minutos con Juan, director de una oficina con 35 asesores en Monterrey"
- "Harvey, me dijeron 'está muy caro', ¿qué respondo?"
- "Harvey, escríbeme el seguimiento por WhatsApp para Ana, la llamada fue ayer"
- "Harvey, arma la propuesta para Inmobiliaria X"
- "Harvey, hagamos role play: tú eres un director escéptico de franquicia"
- "Harvey, registra este lead" / "¿A quién le doy seguimiento hoy?"

Los leads se guardan en `harvey/leads.csv`, que funciona como un mini CRM.

## 2. Harvey chatbot (atiende a tus clientes)
Las instrucciones están en `harvey/harvey-chatbot-prompt.md`. Las pegas en la plataforma de
chatbot que elijas (web, WhatsApp, Instagram).

## Pendiente
En `.claude/skills/harvey/references/r2cp-modelo.md`, agrega tus casos de éxito reales
(solo los usa el copiloto; el chatbot nunca los menciona).
