# ARIADNA · Sigue el hilo

Acompañamiento de arranque de carrera de 5 semanas para asesores inmobiliarios nuevos de las oficinas miembro de R²CP Elite. Acompaña al curso Fast Track Residencial.

- PHP 8.1+ y MySQL/MariaDB, sin frameworks ni compilación. Se instala en hosting compartido de Hostinger.
- Instalación: `docs/GUIA_INSTALACION_HOSTINGER.md`
- Manual para el director: `docs/MANUAL_DIRECTOR.md`

## Estructura
- `hoy.php`: pantalla del asesor (enlace personal `?t=`).
- `oficina.php`, `asesor.php`, `config_oficina.php`: panel de la oficina.
- `admin*.php`, `export.php`: panel del administrador.
- `inc/engine.php`: calendario personal, semáforo y evaluación de 100 puntos.
- `inc/cron_tasks.php` + `cron/run.php`: reporte semanal, alertas, punto de control y recordatorios.
- `install/`: estructura de la base de datos, plan inicial y datos de prueba (se borra después de instalar).
