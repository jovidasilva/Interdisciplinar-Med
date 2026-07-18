<?php
// Wrapper simples para envio de e-mails via PHPMailer usando SMTP.
//
// Configuração lida de variáveis de ambiente através da função env() já
// definida em cfg/config.php (que também aceita um arquivo .env na raiz):
//   SMTP_HOST   (default: mailpit)
//   SMTP_PORT   (default: 1025)
//   SMTP_USER   (default: '' -> sem autenticação, ex: Mailpit)
//   SMTP_PASS   (default: '')
//   SMTP_FROM   (default: noreply@intermed.local)
//   SMTP_SECURE (default: '' -> nenhuma criptografia; aceita "tls" ou "ssl")

require_once __DIR__ . '/../cfg/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Envia um e-mail em HTML via SMTP.
 *
 * @param string $destinatario Endereço de e-mail do destinatário.
 * @param string $assunto Assunto do e-mail.
 * @param string $corpoHtml Corpo do e-mail em HTML.
 * @return bool true se o e-mail foi enviado com sucesso, false caso contrário.
 */
function enviar_email(string $destinatario, string $assunto, string $corpoHtml): bool
{
    $mail = new PHPMailer(true);

    try {
        $host = env('SMTP_HOST', 'mailpit');
        $port = (int) env('SMTP_PORT', '1025');
        $user = env('SMTP_USER', '');
        $pass = env('SMTP_PASS', '');
        $from = env('SMTP_FROM', 'noreply@intermed.local');
        $secure = strtolower(trim(env('SMTP_SECURE', '')));

        $mail->isSMTP();
        $mail->Host = $host;
        $mail->Port = $port;
        $mail->CharSet = 'UTF-8';

        if ($user !== '') {
            $mail->SMTPAuth = true;
            $mail->Username = $user;
            $mail->Password = $pass;
        } else {
            // Sem credenciais configuradas: assume um servidor SMTP local
            // sem autenticação (ex: Mailpit em desenvolvimento).
            $mail->SMTPAuth = false;
        }

        if ($secure === 'tls' || $secure === 'starttls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif ($secure === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            // Mailpit (e outros servidores de teste locais) não usa TLS.
            $mail->SMTPSecure = false;
            $mail->SMTPAutoTLS = false;
        }

        $mail->setFrom($from, 'Internato Med');
        $mail->addAddress($destinatario);

        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body = $corpoHtml;
        $mail->AltBody = trim(strip_tags($corpoHtml));

        $mail->send();
        return true;
    } catch (PHPMailerException $e) {
        error_log('Falha ao enviar e-mail (PHPMailer): ' . $mail->ErrorInfo);
        return false;
    } catch (\Throwable $e) {
        error_log('Falha ao enviar e-mail: ' . $e->getMessage());
        return false;
    }
}
