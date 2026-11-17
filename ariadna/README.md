# ARIADNA · Sigue el hilo

Ariadna es la Team Leader digital de los asesores nuevos en el curso Fast Track Residencial (5 semanas) de las oficinas miembro de R²CP Elite, by Oscar Márquez Seminars.

- PHP 8.1+ y MySQL/MariaDB, sin frameworks ni compilación. Se instala en hosting compartido de Hostinger.
- Instalación: `docs/GUIA_INSTALACION_HOSTINGER.md`
- Manual para el director: `docs/MANUAL_DIRECTOR.md`

## Estructura
- `hoy.php` + `inc/hoy_view.php`: pantalla del asesor (enlace personal `?t=`), metas, «ayer no me contaste».
- `admin_preview.php`: «Ver como asesor» cualquier semana y día.
- `sesiones.php`: temas de la oficina con fecha y hora; Open House o Recorrido en vivo.
- `inc/push.php`, `push_subscribe.php`, `sw.js`: notificaciones al celular (Web Push con VAPID, sin librerías).
- `oficina.php`, `asesor.php`, `config_oficina.php`: panel de la oficina.
- `admin*.php`, `export.php`: panel del administrador.
- `inc/engine.php`: calendario personal, semáforo y evaluación de 100 puntos.
- `inc/cron_tasks.php` + `cron/run.php`: reporte semanal, alertas, punto de control, metas bajas, temas sin fecha y avisos de la mañana y de la noche.
- `install/`: estructura de la base de datos, plan inicial y datos de prueba (se borra después de instalar).
