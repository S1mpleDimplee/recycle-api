<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


function emailTemplate(string $title, string $bodyHtml): string
{
    return '<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>' . htmlspecialchars($title) . '</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f4;font-family:\'Segoe UI\',Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f4;padding:40px 16px;">
    <tr><td align="center">
      <table width="100%" cellpadding="0" cellspacing="0" style="max-width:540px;">

        <tr>
          <td align="center" style="background:#2e7d32;border-radius:16px 16px 0 0;padding:32px 40px 24px;">
            <span style="font-size:28px;font-weight:800;color:#ffffff;letter-spacing:-0.5px;">Tradr</span>
          </td>
        </tr>

        <tr>
          <td style="background:#ffffff;padding:36px 40px;border-radius:0 0 16px 16px;box-shadow:0 4px 24px rgba(0,0,0,0.07);">
            ' . $bodyHtml . '
            <hr style="border:none;border-top:1px solid #e8f5e9;margin:28px 0 20px;">
            <p style="margin:0;font-size:12px;color:#9e9e9e;text-align:center;">
              Dit bericht is automatisch verstuurd door Tradr. Niet beantwoorden.
            </p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>';
}

function createMailer()
{
    $envPath = __DIR__ . '/../../.env';
    if (file_exists($envPath)) {
        foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (str_starts_with(trim($line), '#')) continue;
            [$key, $val] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($val);
        }
    }

    $mail = new PHPMailer(true);
    $mail->CharSet   = 'UTF-8';
    $mail->isSMTP();
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->SMTPDebug  = 0;
    $mail->SMTPAuth   = true;
    $mail->Host       = $_ENV['MAIL_HOST'] ?? '';
    $mail->Port       = (int)($_ENV['MAIL_PORT'] ?? 587);
    $mail->Username   = $_ENV['MAIL_USER'] ?? '';
    $mail->Password   = $_ENV['MAIL_PASS'] ?? '';
    $mail->setFrom($_ENV['MAIL_USER'] ?? '', $_ENV['MAIL_FROM_NAME'] ?? 'Tradr');
    return $mail;
}
