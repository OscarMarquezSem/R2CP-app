# Actualizaciones de Ariadna

## Actualización 3 (`ariadna_actualizacion_3.zip`) · acumulada: incluye la 1 y la 2

**Qué trae:**
1. Videos con «Aparta X: video y tarea».
2. Rutina diaria: «Usa 1 de los 3 diálogos para llamar que vienen en tu hoja de Mi Mapa…» y mención de los diálogos en S1 D2 y D3.
3. S2 D4: «Analiza 3 propiedades de la oficina…», con su guía; mínimo de la Semana 2 = 3.
4. Guías nuevas: inventario (S3 D3), invitación al Open House (S3 D4) y las 17 actividades que no tenían guía.
5. La imagen de Ariadna: su rostro en cada mensaje, su imagen en la bienvenida y como ícono de la app y de las notificaciones.
6. Ariadna celebrando al guardar el día (y «¡Cerraste tu Semana N!» al terminar el repaso semanal), relajada con su café en los días de descanso y con su diploma al terminar el curso.
7. «Ver como asesor» muestra también la celebración y la graduación.

**Archivos:** `hoy.php`, `admin_preview.php`, `inc/hoy_view.php`, `inc/app.php`, `assets/style.css`, `assets/ariadna*.jpg` (5 imágenes), `assets/icon-192.png`, `assets/icon-512.png`, `sw.js` y `actualizar_plan.php` (de un solo uso).

**Cómo aplicarla:**
1. Administrador de archivos → carpeta de Ariadna → sube el zip → Extract con «Overwrite existing files».
2. Entra como administrador y abre `https://ariadna.oscarmarquez.com/actualizar_plan.php`.
3. Borra `actualizar_plan.php` y el zip.

`ariadna_hostinger.zip` (instalación nueva) ya trae todo incluido.

## Actualización 4 (`ariadna_actualizacion_4.zip`)

**Qué cambia:** las imágenes de Ariadna nunca se salen de su recuadro (aunque el navegador tenga estilos viejos guardados) y la app busca siempre primero los estilos más recientes.

**Archivos:** `inc/hoy_view.php` (va dentro de `inc`) y `sw.js` (va suelto en la carpeta de Ariadna).

## Actualización 5 (`ariadna_actualizacion_5.zip`)

**Qué trae:**
1. **Citas con el Team Leader en la oficina** (tipo de actividad nuevo «Cita con tu Team Leader»): 15 en el curso. Una el día 1 (bienvenida y agenda de horarios), una el mismo día de cada video, después de verlo (los 7 módulos y los 3 videos cortos), y el cierre del día 5 de cada semana.
2. La bienvenida de la Semana 1 deja de ser un tema que programa la oficina: ahora es la cita con el TL (quedan 11 temas de oficina).
3. Ariadna le insiste al asesor en ir a su oficina (bienvenida, mensaje del día y «Recuerda» de cada semana).
4. Panel de la oficina: «Hoy vienen a la oficina con su Team Leader» y columna «Citas con TL». Detalle del asesor: a cuántas citas fue y a cuáles faltó. Reporte semanal: «Citas con su TL».
5. Semáforo: si falta a 1 cita, Ariadna lo sugiere; si falta a 2, amarillo. Solo cuentan las citas desde el día en que se aplica la actualización.
6. Dar de baja: solo director y gerente. «No continúa»: cualquiera de los tres. Motivo obligatorio en los dos y sección «Movimientos» en el detalle del asesor.

**Archivos:** `actualizar_plan.php` (de un solo uso), `asesor.php`, `oficina.php`, `inc/app.php`, `inc/engine.php`, `inc/hoy_view.php`, `inc/cron_tasks.php`.

**Cómo aplicarla:**
1. Administrador de archivos → carpeta de Ariadna → sube el zip → Extract (escribe el nombre `temporal`) → mueve los archivos a su lugar (los de `inc` dentro de `inc`).
2. Entra como administrador y abre `https://ariadna.oscarmarquez.com/actualizar_plan.php`.
3. Borra `actualizar_plan.php`, la carpeta `temporal` y el zip.

## Actualización 6 (`ariadna_actualizacion_6.zip`) · acumulada: incluye la 5

**Qué trae:**
1. **Todo lo de la Actualización 5:** citas con el Team Leader, permisos de baja con motivo y «Movimientos». Si no subiste la 5, no hace falta: esta la incluye.
2. **Reto del video:** 5 preguntas con respuesta clave por cada uno de los 7 módulos, tomadas de la transcripción. El TL las aplica con el botón **Aplicar Reto**. Valen 10 puntos de la evaluación; el cierre de renta pasa de 20 a 10.
3. **Jornada de opinión de valor:** tercera opción en Sesiones, para oficinas que no hacen Open House ni Recorrido en vivo.
4. **El plan dice lo mismo que los videos:**
   - Semana 1: 3 propiedades (2 en venta y 1 en renta) en todas las redes y en 5 grupos, 1 contenido de valor y WhatsApp Business.
   - Semana 2: foto y biografía.
   - Ritmo de cada semana: 3 propiedades y 1 contenido. Mínimos de promovidas: 3 y 3; semáforo de la Semana 2: 6.
   - Semana 4: identificar y llamar 20 tratos directos (mínimo 20).
   - Semana 5: organizar y clasificar contactos, 10 mensajes con pretexto de valor, 3 citas con propietarios (mínimo 3) y roleplay de las 5 objeciones.
5. **Correcciones:** en el detalle del asesor, las preguntas del repaso mostraban `{OH}`; y una tabla se salía del ancho del celular.

**Archivos (13):**
- Sueltos en la carpeta de Ariadna: `actualizar_plan.php` (de un solo uso), `reto.php` (nuevo), `asesor.php`, `oficina.php`, `sesiones.php`, `admin_plan.php`, `admin_ajustes.php`, `export.php`.
- Dentro de `inc`: `app.php`, `engine.php`, `hoy_view.php`, `cron_tasks.php`.
- Dentro de `assets`: `style.css`.

**Cómo aplicarla:**
1. Administrador de archivos → carpeta de Ariadna → sube el zip → Extract (escribe el nombre `temporal`).
2. Mueve los archivos a su lugar: los de `inc` dentro de `inc`, `style.css` dentro de `assets` y los demás sueltos en la carpeta de Ariadna.
3. Entra como administrador y abre `https://ariadna.oscarmarquez.com/actualizar_plan.php`. Debe decir «Se aplicaron N cambios»: unos 52 si no subiste la 5, o unos 44 si ya la habías subido.
4. Borra `actualizar_plan.php`, la carpeta `temporal` y el zip.

## Próxima actualización (se junta con lo que salga de la prueba)

| # | Cambio | Archivo |
|---|---|---|
| 2 | Punto de control visible para el asesor al inicio de la Semana 4 (qué cumplió, qué le faltó, «tu oficina lo revisa contigo»), sin detenerlo. Recordatorio diario a la oficina mientras no registre «continúa» o «no continúa». | Aprobado |
| 3 | Mensaje de bienvenida por WhatsApp editable en Ajustes ({nombre}, {oficina}, {enlace}), con el texto nuevo propuesto. | Pendiente de visto bueno del texto |
| 4 | Programas por oficina (Ariadna y/o Édalo) y programa por asesor; panel con pestañas Todos · Ariadna · Édalo. | Aprobado (opción A) |
| 1 | Preguntas del Reto para los 3 videos cortos (conceptos básicos, opinión de valor, perfilamiento), cuando estén grabados y transcritos. | Plan (se agregan desde el panel) |
