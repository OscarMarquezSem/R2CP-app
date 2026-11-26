# Harvey en WhatsApp con Make

Cómo conectar Harvey a WhatsApp usando Make, para que atienda a los leads que llegan de tus
redes, de tus lead magnets y de tus pautas en Meta.

---

## 1. Cómo funciona

```
 Redes / lead magnets ──► enlace wa.me con mensaje ya escrito ─┐
 Pauta Meta "Clic a WhatsApp" ────────────────────────────────┤
                                                              ▼
                                   WhatsApp de HARVEY (número del bot)
                                                              │
                                        MAKE · Escenario 1 "Harvey conversa"
                                 1. Recibe el mensaje
                                 2. Recupera el historial de ese número
                                 3. Claude responde como Harvey
                                 4. Envía la respuesta por WhatsApp
                                 5. Guarda el historial
                                 6. Si el lead ya está calificado:
                                      → lo registra en Google Sheets
                                      → te avisa a ti (+52 55 6215 4614)

 Formulario condicional de Meta (opcional, se puede seguir usando)
        └─► MAKE · Escenario 2 "Lead de formulario"
               → registra en Google Sheets
               → Harvey le escribe primero por WhatsApp (con una plantilla aprobada)
```

### Decisiones importantes

1. **Usa un número de WhatsApp distinto para Harvey.** Tu número (+52 55 6215 4614) sigue
   siendo para agendar contigo. Harvey atiende en su propio número y, cuando el lead califica,
   le manda tu enlace y te avisa. Así tus chats personales nunca pasan por el bot.
   (Meta permite en algunos casos usar el mismo número en la app y en la API, la llamada
   "coexistencia", pero mezclar el bot con tus chats personales no es recomendable.)
2. **Hace falta la API oficial (WhatsApp Business Cloud API)**, conectada desde tu Meta Business
   Manager. La app normal de WhatsApp Business no se puede conectar a Make.
3. **La ventana de 24 horas de WhatsApp:** Harvey puede contestar libremente durante las 24 horas
   siguientes al último mensaje del lead. Para escribirle primero (Escenario 2) o dar seguimiento
   después de 24 horas, se necesitan **plantillas aprobadas por Meta**.
4. **Pautas:** cambia el objetivo de tus campañas a **"Clic a WhatsApp"** con el número de Harvey.
   Tu formulario condicional ya filtraba por número de asesores; ahora el filtro lo hace Harvey
   dentro de la conversación.

### Lo que necesitas tener antes
- [ ] Cuenta de **Make** (el plan Core o superior alcanza).
- [ ] **Meta Business Manager** verificado y un **número nuevo** dado de alta en la WhatsApp Business Cloud API.
- [ ] **API key de Anthropic (Claude)** desde console.anthropic.com, con saldo cargado.
- [ ] Una **hoja de Google Sheets** "Leads R2CP" (Harvey te la puede crear).

### Enlaces para tus lead magnets
Cada lead magnet lleva un enlace con un mensaje distinto, para que Harvey sepa de dónde viene
cada lead (reemplaza `NUMERO_HARVEY` por el número del bot, sin + ni espacios):

| Origen | Enlace |
|---|---|
| Bio de Instagram | `https://wa.me/NUMERO_HARVEY?text=Hola,%20vengo%20de%20Instagram%20y%20quiero%20saber%20de%20R2CP` |
| Lead magnet 1 | `https://wa.me/NUMERO_HARVEY?text=Hola,%20quiero%20el%20recurso%20[NOMBRE]` |
| Webinar | `https://wa.me/NUMERO_HARVEY?text=Hola,%20vi%20el%20webinar%20y%20quiero%20mi%20asesoría` |

---

## 2. Escenario 1: "Harvey conversa"

| # | Módulo de Make | Configuración |
|---|---|---|
| 1 | **WhatsApp Business Cloud › Watch Events** | Número de Harvey. Filtro: solo mensajes de tipo *text* (ignora estados y confirmaciones de lectura). |
| 2 | **Data store › Get a record** | Data store `harvey_conversaciones` (campos: `telefono` como clave, `historial` texto, `calificado` sí/no, `actualizado` fecha). Clave = teléfono del remitente. |
| 3 | **Anthropic Claude › Create a Message** | Modelo: `claude-sonnet-5-5`. Máximo de tokens: 600. **System:** el prompt de `harvey-chatbot-prompt.md` + el **anexo de WhatsApp** (sección 4). **Mensajes:** el historial guardado + el mensaje nuevo del usuario. |
| 4 | **Text parser › Match pattern** | Busca el bloque `<<LEAD>>...<<FIN>>` en la respuesta de Claude (regex: `<<LEAD>>([\s\S]*?)<<FIN>>`). |
| 5 | **Tools › Set variable** | `respuesta_limpia` = respuesta de Claude **sin** el bloque `<<LEAD>>…<<FIN>>` (usa `replace` con el mismo regex). |
| 6 | **WhatsApp Business Cloud › Send a Message** | Para: el teléfono del remitente. Texto: `respuesta_limpia`. |
| 7 | **Data store › Add/replace a record** | Guarda el historial actualizado (mensaje del usuario + respuesta). Conserva los últimos 30 mensajes. |
| 8 | **Router**, con una ruta que solo pasa si el módulo 4 encontró el bloque `<<LEAD>>` |
| 8a | **JSON › Parse JSON** | Convierte el contenido del bloque en campos. |
| 8b | **Google Sheets › Add a row** | Hoja "Leads R2CP" con las columnas de la sección 5. |
| 8c | **WhatsApp › Send a Message** (o **Gmail › Send an email**) | Te avisa: "🔥 Nuevo lead R2CP: {nombre}, {oficina}, {asesores} asesores, {pais}. Dolor: {pilar}. Puntaje: {puntaje}/16." Si te avisa por WhatsApp a tu número, necesita una plantilla aprobada. |
| 8d | **Data store › Update a record** | `calificado` = sí (para no registrar el mismo lead dos veces). |

**Manejo de errores:** agrega un manejador de error en el módulo 3. Si Claude falla, que Harvey
responda: "Dame un momento, en breve te respondo 🙏" y te avise.

**Pasar la conversación a una persona:** si el lead escribe "hablar con Oscar" o "asesor humano",
Harvey le manda tu enlace `https://wa.me/525562154614` y el escenario te avisa.

---

## 3. Escenario 2: "Lead de formulario de Meta" (opcional)

Úsalo si quieres seguir usando tu formulario condicional en algunas campañas.

| # | Módulo | Configuración |
|---|---|---|
| 1 | **Facebook Lead Ads › Watch Leads** | Tu página y tu formulario condicional. |
| 2 | **Google Sheets › Add a row** | Etapa = `nuevo`, origen = `formulario Meta`. |
| 3 | **Router** | Si tiene 20 o más asesores → ruta A. Si tiene menos → ruta B. |
| 3A | **WhatsApp › Send a Template Message** | Plantilla `harvey_bienvenida`: "Hola {{1}}, soy Harvey, asistente de Oscar Márquez. Vi que dejaste tus datos sobre R2CP para tu oficina. ¿Te puedo hacer 2 preguntas rápidas para ver cómo ayudarte?" Cuando el lead responda, sigue el Escenario 1. |
| 3B | **WhatsApp › Send a Template Message** | Plantilla `harvey_cursos`: "Hola {{1}}, gracias por tu interés. Te regalo acceso a todos los cursos gratuitos de Oscar: oscarmarquez.com/universidad" |
| 4 | **Data store › Add a record** | Guarda en el historial el mensaje de la plantilla para que Harvey tenga contexto. |

---

## 4. Anexo del prompt para WhatsApp

Agrega este texto **al final** del prompt de `harvey-chatbot-prompt.md` en el módulo de Claude:

```
FORMATO WHATSAPP
- Máximo 3–4 líneas por mensaje. Una sola pregunta a la vez.
- Puedes usar *negritas* de WhatsApp y 1 emoji como máximo. Nada de títulos ni tablas.
- Si el primer mensaje menciona de dónde viene (Instagram, webinar, un recurso), úsalo para conectar.

REGISTRO DEL LEAD (invisible para el usuario)
Cuando ya sepas si el prospecto califica (sabes # de asesores, su rol y su dolor principal),
y SOLO UNA VEZ por conversación, agrega al final de tu respuesta este bloque exacto, con JSON válido:
<<LEAD>>{"nombre":"","oficina":"","pais":"","ciudad":"","asesores":0,"rol":"","pilar_dolor":"","meta_12_meses":"","origen":"","califica":true,"puntaje_oden":0,"siguiente_paso":""}<<FIN>>
Nunca menciones este bloque ni hables de él.
```

---

## 5. Columnas de la hoja "Leads R2CP"

`fecha | telefono | nombre | oficina | pais | ciudad | asesores | rol | pilar_dolor | meta_12_meses | origen | califica | puntaje_oden | etapa | siguiente_paso | fecha_seguimiento | notas`

---

## 6. Prompt para construirlo con IA

Copia este texto en **Claude con el conector de Make conectado** (Settings → Connectors → Make)
o en el asistente de IA de Make.

```
Quiero que construyas en Make dos escenarios para "Harvey", mi agente de ventas de R2CP por
WhatsApp. Sigue exactamente este diseño:

CONTEXTO
Soy Oscar Márquez, conferencista y capacitador inmobiliario. Vendo R2CP, un modelo de
escalamiento para directores de oficinas inmobiliarias con 20+ asesores. Los leads llegan
por WhatsApp desde mis redes, lead magnets y pautas de Meta "Clic a WhatsApp".

CONEXIONES (ya creadas en mi cuenta de Make; si falta alguna, dime cuál crear)
- WhatsApp Business Cloud (número de Harvey)
- Anthropic Claude (mi API key)
- Google Sheets (hoja "Leads R2CP")
- Facebook Lead Ads (solo para el escenario 2)

DATA STORE
Crea "harvey_conversaciones" con: telefono (clave, texto), historial (texto largo, JSON con
los mensajes [{role, content}]), calificado (booleano), actualizado (fecha).

ESCENARIO 1 – "Harvey conversa"
1. WhatsApp Business Cloud > Watch Events. Filtra solo mensajes entrantes de tipo texto.
2. Data store > Get a record con clave = teléfono del remitente.
3. Anthropic Claude > Create a Message. Modelo claude-sonnet-5-5, max_tokens 600.
   System = [PEGAR AQUÍ el prompt de harvey-chatbot-prompt.md + el anexo de WhatsApp].
   Messages = historial guardado (si existe) + {role:"user", content: texto recibido}.
4. Text parser > Match pattern con el regex <<LEAD>>([\s\S]*?)<<FIN>> sobre la respuesta.
5. Quita ese bloque de la respuesta (replace con el mismo regex por vacío) y trim.
6. WhatsApp Business Cloud > Send a Message al remitente con la respuesta limpia.
7. Data store > Add/replace record: agrega al historial el mensaje del usuario y la
   respuesta limpia de Harvey; conserva solo los últimos 30 mensajes; actualizado = now.
8. Router. Ruta "Lead calificado" con filtro: el paso 4 encontró coincidencia Y calificado
   del data store no es true:
   a. JSON > Parse JSON del grupo capturado.
   b. Google Sheets > Add a row con: fecha=now, telefono, nombre, oficina, pais, ciudad,
      asesores, rol, pilar_dolor, meta_12_meses, origen, califica, puntaje_oden,
      etapa="calificado", siguiente_paso.
   c. Gmail > Send an email a mi correo con asunto "🔥 Nuevo lead R2CP: {nombre} – {oficina}"
      y el resumen del lead.
   d. Data store > Update record: calificado = true.
9. Manejador de error en el paso 3: enviar por WhatsApp "Dame un momento, en breve te
   respondo 🙏" y mandarme un correo con el error.

ESCENARIO 2 – "Lead de formulario Meta"
1. Facebook Lead Ads > Watch Leads (mi formulario condicional).
2. Google Sheets > Add a row (etapa "nuevo", origen "formulario Meta").
3. Router por número de asesores: >=20 → plantilla WhatsApp "harvey_bienvenida" con el
   nombre; <20 → plantilla "harvey_cursos".
4. Data store > Add/replace record con el texto de la plantilla como primer mensaje
   {role:"assistant"} del historial.

AL TERMINAR
- Deja ambos escenarios desactivados para que yo los pruebe primero.
- Dame la lista de lo que yo debo hacer a mano (aprobar plantillas en Meta, conectar
  cuentas, pegar la API key) y cómo probarlo con mi propio WhatsApp.
```

---

## 7. Cómo probarlo
1. Activa el Escenario 1 en modo "Run once" y escríbele a Harvey desde tu celular.
2. Haz 3 pruebas: un director con 35 asesores, uno con 8 asesores, y alguien que insiste en el precio.
3. Revisa que: no diga precios, mande tu enlace a quien califica, mande los cursos gratis a quien
   no califica, aparezca la fila en Google Sheets y te llegue el aviso.
4. Si todo está bien, activa el escenario y cambia tus pautas a "Clic a WhatsApp".
