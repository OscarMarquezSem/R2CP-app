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
1. **Citas con el Team Leader en la oficina** (tipo de actividad nuevo «Cita con tu Team Leader»): 11 en el curso. Semana 1: días 1 (bienvenida y agenda de horarios), 3 (repaso del Módulo 1) y 5 (cierre). Semanas 2 a 5: un repaso de lo que aprendió en los videos y el cierre de semana.
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

## Próxima actualización (se junta con lo que salga de la prueba)

| # | Cambio | Archivo |
|---|---|---|
| — | Aún no hay cambios pendientes. | |
