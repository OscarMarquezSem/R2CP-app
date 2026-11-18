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

## Próxima actualización (aún no se arma el zip; se junta con lo que salga de la prueba)

| # | Cambio | Archivo |
|---|---|---|
| 1 | Dar de baja a un asesor: solo el director y el gerente. Registrar «continúa / no continúa» en el punto de control: cualquiera de los tres. | `asesor.php` |
