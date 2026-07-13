# Configuración para producción e InfinityFree

## 1. Base de datos
Edite `config/app.php` y cambie los valores de `database` por los datos entregados por el panel de InfinityFree. En ese hosting el servidor MySQL normalmente no es `localhost`; use exactamente el hostname mostrado en **MySQL Databases**.

## 2. URL pública
En `app.base_url` indique la URL HTTPS final, por ejemplo:

```php
'base_url' => 'https://tudominio.com',
```

Cambie `environment` a `production` para impedir que el enlace de recuperación se muestre en pantalla cuando falle el envío.

## 3. Correo SMTP
El hosting gratuito no debe depender de `mail()`. Configure un proveedor SMTP externo en `config/app.php`:

```php
'mail' => [
    'enabled' => true,
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'encryption' => 'tls',
    'username' => 'cuenta@gmail.com',
    'password' => 'CLAVE_DE_APLICACION',
    'from_email' => 'cuenta@gmail.com',
    'from_name' => 'CyberData',
    'timeout' => 20,
],
```

Para Gmail debe utilizarse una **contraseña de aplicación**, no la contraseña normal de la cuenta. También puede emplearse otro proveedor SMTP compatible con TLS en el puerto 587.

## 4. Prueba local
Mientras `environment` sea `development` y `mail.enabled` sea `false`, la pantalla muestra el enlace temporal solo para facilitar las pruebas en XAMPP.

## 5. Archivos que deben cargarse
Suba todo el contenido de la carpeta `CyberData` al directorio `htdocs` del hosting. No cargue respaldos ni archivos con credenciales distintas de las que utilizará en producción.

## 6. Seguridad final
Antes de publicar:

- Active HTTPS y configure `base_url` con `https://`.
- Cambie `environment` a `production`.
- Configure SMTP externo.
- Use credenciales MySQL exclusivas del hosting.
- Compruebe que `token_recovery` y `token_expira` existan en `usuario`.
- Pruebe que un enlace expire después de 15 minutos y que no pueda reutilizarse.
