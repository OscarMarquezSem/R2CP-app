# Guía de instalación de Ariadna en Hostinger

Tiempo aproximado: 30 a 40 minutos. No necesitas saber programar. Haz los pasos en orden.

Necesitas:
- Tu acceso a **hPanel** de Hostinger.
- El archivo **ariadna.zip** (la carpeta de la app).

---

## Paso 1. Crea el subdominio

1. En hPanel entra a **Dominios → Subdominios**.
2. Escribe `ariadna` y elige `oscarmarquez.com`.
3. Deja la carpeta que propone Hostinger (algo como `public_html/ariadna`) y da clic en **Crear**.

## Paso 2. Activa el candado de seguridad (HTTPS)

1. Ve a **Seguridad → SSL**.
2. Busca `ariadna.oscarmarquez.com` e instala el **SSL gratuito**. Puede tardar unos minutos en activarse.

## Paso 3. Revisa la versión de PHP

1. Ve a **Avanzado → Configuración de PHP**.
2. Elige **PHP 8.1 o mayor** (recomendado 8.2 o 8.3).
3. En la pestaña de extensiones, verifica que estén marcadas **pdo_mysql**, **gd**, **mbstring**, **curl** y **openssl**. Normalmente ya lo están.

## Paso 4. Crea la base de datos

1. Ve a **Bases de datos → Administración** (MySQL).
2. Llena:
   - Nombre de la base de datos: `ariadna`
   - Usuario: `ariadna`
   - Contraseña: una contraseña larga. **Guárdala.**
3. Da clic en **Crear**.
4. Anota los nombres completos que muestra Hostinger. Llevan un prefijo, por ejemplo `u123456789_ariadna`. Los vas a necesitar en el Paso 7.

## Paso 5. Crea el buzón de correo que enviará los reportes

1. Ve a **Correos → Cuentas de correo** y crea `ariadna@oscarmarquez.com` con una contraseña. **Guárdala.**
2. Los datos de envío (SMTP) de Hostinger son:
   - Servidor: `smtp.hostinger.com`
   - Puerto: `465`
   - Seguridad: SSL

## Paso 6. Sube los archivos

1. Ve a **Archivos → Administrador de archivos**.
2. Abre la carpeta del subdominio, por ejemplo `public_html/ariadna`. Si tiene un archivo `default.php`, bórralo.
3. Sube **ariadna.zip**, dale clic derecho y elige **Extraer**.
4. Revisa que los archivos (`hoy.php`, `login.php`, `install.php`, la carpeta `inc`…) queden **directamente** en la carpeta del subdominio y no dentro de otra subcarpeta. Si quedaron dentro de una carpeta `ariadna`, muévelos un nivel arriba.

## Paso 7. Crea el archivo de configuración

1. En el Administrador de archivos, da clic derecho en **config.sample.php → Copiar**. Nombra la copia **config.php**.
2. Abre **config.php** con **Editar** y cambia lo siguiente:
   - `base_url`: `https://ariadna.oscarmarquez.com`
   - En `db`: el nombre de la base de datos, el usuario y la contraseña del Paso 4, con su prefijo.
   - En `mail`: el correo y la contraseña del Paso 5, en `user`, `pass` y `from`.
   - `cron_key` y `app_secret`: escribe en cada uno una frase larga cualquiera, distinta entre sí, por ejemplo `hilo-rojo-cancun-2026-mar-azul`. No las compartas.
3. Guarda.

## Paso 8. Instala

1. Abre en tu navegador **https://ariadna.oscarmarquez.com/install.php**
2. Escribe tu nombre, tu correo y una contraseña de al menos 10 caracteres. Esa será tu cuenta de **administrador**.
3. Deja marcada la casilla **Cargar datos de prueba** si quieres revisar la app con 2 oficinas y 6 asesores de ejemplo.
4. Da clic en **Instalar Ariadna**. La pantalla te muestra los usuarios de prueba y los enlaces de los asesores de prueba.
5. **Muy importante:** regresa al Administrador de archivos y **borra `install.php` y la carpeta `install`**.

## Paso 9. Programa las tareas automáticas (cron)

Esto envía el reporte semanal, las alertas de rojo, el punto de control y los recordatorios.

1. Ve a **Avanzado → Trabajos Cron**.
2. Elige **PHP** e indica la ruta del archivo. La ruta completa se ve en la parte de arriba del Administrador de archivos. Queda algo así:
   ```
   /home/u123456789/domains/oscarmarquez.com/public_html/ariadna/cron/run.php
   ```
   Si Hostinger te pide el comando completo, escribe:
   ```
   /usr/bin/php /home/u123456789/domains/oscarmarquez.com/public_html/ariadna/cron/run.php
   ```
3. Frecuencia: **cada hora** (minuto `0`, todas las horas, todos los días).
4. Guarda.

> El reporte semanal sale el **viernes a las 6 pm, hora de Cancún**. Lo puedes cambiar en **Ajustes**.

## Paso 10. Prueba el correo

1. Entra a **https://ariadna.oscarmarquez.com/login.php** con tu cuenta de administrador.
2. Ve a **Ajustes** y, hasta abajo, da clic en **Enviarme un correo de prueba**.
3. Si no llega, revisa en `config.php` el correo y la contraseña del buzón. En la misma pantalla de Ajustes aparece el error.

## Paso 11. Revisa el curso día por día

1. En tu panel entra a **Ver como asesor**.
2. Elige semana y día, o una pantalla especial (bienvenida, descanso, capacitación interna), y recorre los 25 días con **Día anterior / Día siguiente**.
3. Lo que quieras cambiar (textos, guías, videos, mínimos) lo editas en **Plan**. Nada de lo que veas en la vista previa se guarda.

## Paso 12. Prueba un video y las notificaciones en tu celular

1. Abre en tu celular el enlace de una asesora de prueba (aparecen al terminar la instalación) y ve a una actividad de video: debe reproducirse **dentro de Ariadna**.
2. Agrega Ariadna a tu pantalla de inicio y toca **Activar avisos de Ariadna**. En iPhone esto solo funciona si la abriste desde la pantalla de inicio.

## Paso 13. Protege los videos en Bunny (al final)

Cuando ya viste que los videos se reproducen dentro de Ariadna:
1. En Bunny, abre tu biblioteca de videos → **Security**.
2. En **Allowed Domains** (o "Allowed Referrers") agrega `ariadna.oscarmarquez.com`.
3. Si aparece **Block direct URL file access**, actívalo.
4. Vuelve a abrir un video en Ariadna para confirmar que se sigue viendo. Desde otra página o con el enlace directo ya no se verá.

## Paso 14. Arranca con oficinas reales

1. Cuando termines de revisar, ve a **Ajustes → Borrar datos de prueba**.
2. En **Oficinas → + Nueva oficina**, crea cada oficina miembro.
3. En **Editar y usuarios**, da de alta al director, al gerente y al team leader. Ariadna te propone una contraseña inicial. Compártesela a cada uno; después la pueden cambiar en **Mi cuenta**.
4. Cada oficina programa sus **Sesiones** (temas internos con fecha y hora) y elige **Open House o Recorrido en vivo**.
5. El director da de alta a sus asesores desde su panel y les envía su enlace por WhatsApp.

---

## Cómo actualizar Ariadna más adelante

Sube los archivos nuevos encima de los anteriores, **sin** reemplazar `config.php` ni la carpeta `uploads`. Tus datos viven en la base de datos y no se pierden.

## Respaldo

En hPanel, **Archivos → Respaldos**, puedes descargar la base de datos. Te recomiendo hacerlo una vez al mes.

## Si algo falla

| Lo que ves | Qué revisar |
|---|---|
| «No me pude conectar a la base de datos» | Nombre, usuario y contraseña con su prefijo en `config.php`. |
| Página en blanco | Que la versión de PHP sea 8.1 o mayor (Paso 3). |
| No llegan correos | Correo de prueba en Ajustes; usuario y contraseña del buzón; revisa la carpeta de spam. |
| No llega el reporte del viernes | Que el cron esté activo (Paso 9). En Ajustes ves la lista de correos enviados. |
| El video no se ve dentro de Ariadna | Que el dominio en Bunny sea exactamente `ariadna.oscarmarquez.com` y que la actividad tenga su enlace en **Plan**. |
| No llegan las notificaciones al celular | Que el asesor haya tocado «Activar avisos de Ariadna» y lo haya permitido. En iPhone, debe abrir Ariadna desde la pantalla de inicio. Si no las activa, le llegan por correo. |
