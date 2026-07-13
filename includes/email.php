<?php

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * Envía un correo mediante PHPMailer y la configuración definida en config/app.php.
 *
 * Mantiene la firma smtp_send() para no modificar recuperar_password.php
 * ni otras partes del proyecto.
 */
function smtp_send(
    string $to,
    string $subject,
    string $htmlBody,
    string $textBody = ''
): bool {
    $config = app_config('mail', []);

    if (empty($config['enabled'])) {
        error_log('SMTP deshabilitado: no se envió el correo a ' . $to);
        return false;
    }

    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        error_log('Destinatario SMTP inválido: ' . $to);
        return false;
    }

    $host = trim((string) ($config['host'] ?? ''));
    $port = (int) ($config['port'] ?? 587);
    $encryption = strtolower(trim((string) ($config['encryption'] ?? 'tls')));
    $username = trim((string) ($config['username'] ?? ''));
    $password = (string) ($config['password'] ?? '');
    $fromEmail = trim((string) ($config['from_email'] ?? $username));
    $fromName = trim((string) ($config['from_name'] ?? 'CyberData'));
    $timeout = max(5, (int) ($config['timeout'] ?? 20));

    if (
        $host === ''
        || $username === ''
        || $password === ''
        || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)
    ) {
        error_log('Configuración SMTP incompleta o inválida.');
        return false;
    }

    $mailer = new PHPMailer(true);

    try {
        $mailer->isSMTP();
        $mailer->Host = $host;
        $mailer->Port = $port;
        $mailer->SMTPAuth = true;
        $mailer->Username = $username;
        $mailer->Password = $password;
        $mailer->Timeout = $timeout;
        $mailer->CharSet = PHPMailer::CHARSET_UTF8;

        /*
         * En desarrollo se mantiene el diagnóstico en el log de PHP.
         * No se muestra información SMTP directamente al usuario.
         */
        $environment = (string) app_config('app.environment', 'production');
        if ($environment === 'development') {
            $mailer->SMTPDebug = SMTP::DEBUG_SERVER;
            $mailer->Debugoutput = static function (string $message, int $level): void {
                error_log("PHPMailer SMTP [{$level}]: {$message}");
            };
        } else {
            $mailer->SMTPDebug = SMTP::DEBUG_OFF;
        }

        if ($encryption === 'tls' || $encryption === 'starttls') {
            $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif ($encryption === 'ssl' || $encryption === 'smtps') {
            $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($encryption === '' || $encryption === 'none') {
            $mailer->SMTPSecure = '';
            $mailer->SMTPAutoTLS = false;
        } else {
            throw new Exception('Método de cifrado SMTP no válido.');
        }

        $mailer->setFrom($fromEmail, $fromName);
        $mailer->addAddress($to);

        $mailer->isHTML(true);
        $mailer->Subject = $subject;
        $mailer->Body = $htmlBody;
        $mailer->AltBody = $textBody !== ''
            ? $textBody
            : trim(html_entity_decode(
                strip_tags(
                    str_replace(
                        ['<br>', '<br/>', '<br />', '</p>'],
                        ["\n", "\n", "\n", "\n"],
                        $htmlBody
                    )
                ),
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            ));

        $mailer->send();
        return true;
    } catch (Exception $e) {
        $detail = $mailer->ErrorInfo !== ''
            ? $mailer->ErrorInfo
            : $e->getMessage();

        error_log('Error al enviar correo con PHPMailer: ' . $detail);
        return false;
    }
}
