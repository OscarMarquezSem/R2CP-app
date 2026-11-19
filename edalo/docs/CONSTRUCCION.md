# Edalo · Plan de construcción (registro de decisiones)

Edalo se escribe **sin acento** en todos lados: app, dominio (edalo.oscarmarquez.com), correo (edalo@oscarmarquez.com) y textos.

## Arquitectura (opción A, aprobada)
- **Una sola instalación** en la carpeta de Ariadna, `public_html/ariadna`, con una sola base de datos. El subdominio `edalo.oscarmarquez.com` apunta a esa misma carpeta.
- **Dos programas:**
  - `residencial`: Ariadna, Fast Track Residencial, 5 semanas.
  - `industrial`: Edalo, Fast Track Industrial, 4 semanas.
- **Oficinas:** `offices.programs` guarda los programas activos (`residencial`, `industrial` o los dos). Lo marca el administrador.
- **Asesores:** `advisors.program`. Se elige al dar de alta, solo si la oficina tiene los dos programas.
- **El asesor** ve solo su programa: marca, colores, imágenes, plan, videos, números, evaluación y la URL de su enlace (el `base_url` de su programa).
- **La oficina** tiene un solo usuario y un solo panel, con pestañas Todos · Ariadna · Edalo. Cada asesor lleva su etiqueta y cada pestaña muestra sus columnas. El reporte semanal llega separado por programa.
- **Correo por programa:** cada programa usa su propio remitente (SMTP) en `config.php`.

## Edalo: reglas del curso
- **4 semanas, 12 videos.** Los Videos 2, 3, 5, 6, 9 y 12 son los mismos de Residencial y usan sus mismos Retos.
- **Rutina diaria:** 10 llamadas del Impulsor Industrial desde el día 1. No hay rutina de arranque de 20.
- **Números del día:** llamadas, recorridos, fichas, cadenas, leads y piezas.
- **Metas semanales fijas** (sin «pon tus metas»):

  | Actividad | S1 | S2 | S3 | S4 |
  |---|---|---|---|---|
  | Llamadas | 50 | 50 | 50 | 50 |
  | Recorridos | 2 | 2 | 2 | 2 |
  | Fichas | 2 | 3 | 3 | 2 |
  | Cadenas | 1 | 1 | 2 | 1 |
  | Leads | 1 | 1 | 2 | 1 |
  | Piezas | 1 | 2 | 2 | 1 |

- **Logros:** captación autorizada (15 puntos), oferta formal por escrito (15), inquilino calificado con visita, renta cerrada (10).
- **Filtro al cerrar la S2** (S1 y S2 juntas): 100 llamadas, 4 recorridos y 2 leads.
  - Si no cumple, **repite la S2**.
  - En cuanto cumple (con lo registrado hasta el día anterior), la S3 empieza **al día siguiente**.
  - Se repite **una vez**. Si al terminar la repetición tampoco cumple, avanza a la S3 y la oficina decide si continúa.
  - Mientras repite, el semáforo está en amarillo y la oficina recibe aviso por correo.
- **Punto de control al cerrar la S3:**
  - Requisito: ≥ 50 puntos y, además, al menos 1 de estos 3: 5 leads, 1 captación, o 1 inquilino calificado con visita.
  - Lo decide la oficina: el asesor lo ve con claridad y la oficina recibe un recordatorio diario hasta registrar su decisión.
- **Evaluación (100 puntos):**
  - **Prospección (20):** 5 puntos cada uno, proporcional a la meta del mes: 200 llamadas, 8 recorridos, 10 fichas y 5 cadenas.
  - **Leads (20):** 4 puntos por lead, hasta 5.
  - **Conocimiento (20):**
    - Propiedades levantadas (0 a 7): las califica el TL en la evaluación final, apoyado en los Retos.
    - Zonas y parques (0 a 7): también las califica el TL.
    - Piezas (6): proporcional a 6 piezas.
  - **Resultados (40):** captación 15, oferta 15, renta 10.
  - **Niveles:** 90 o más Top · 70 Va bien · 50 Necesita apoyo · menos de 50 No continúa.
- **Semáforo:**
  - 🟡 2 días hábiles sin registrar · faltó a 2 citas con el TL · repite la S2.
  - 🔴 cerró la S1 sin actividad · no cumple el punto de control de la S3.
- **Citas con el TL:** bienvenida el D1, un Reto después de cada video (los días con 2 videos llevan un Reto de 10 preguntas) y el cierre de cada viernes.
- **Capacitación interna:** sí. **Temas de la oficina:** no.

## Pasos de construcción
1. ☐ Programas: registro de programas, migración de la base de datos (v4) y Ariadna sin cambios (prueba de regresión).
2. ☐ Motor por programa: plan, campos, semanas, evaluación, punto de control y semáforo.
3. ☐ Plan de Edalo (seed y actualizar_plan) y sus Retos.
4. ☐ Calendario con el filtro de la S2 (repetición y apertura al día siguiente).
5. ☐ Marca por programa en la pantalla del asesor: encabezado, colores, bienvenida, mensajes e imágenes.
6. ☐ Oficina: programas por oficina (admin), programa en el alta, pestañas del panel, Plan y «Ver como asesor» por programa.
7. ☐ Correos, recordatorios y reporte por programa. Aviso del punto de control (Ariadna y Edalo).
8. ☐ Prueba completa y Actualización 7, con la guía para el subdominio y el correo.
