<?php
require_once __DIR__ . '/../mail/mailer.php';

function ForgotPassword($data, $conn)
{
    $email = trim($data['email'] ?? '');

    if (empty($email)) {
        echo json_encode(["success" => false, "message" => "E-mailadres is verplicht"]);
        return;
    }

    $stmt = mysqli_prepare($conn, "SELECT id, name FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$user) {
        echo json_encode(["success" => true, "message" => "Als dit e-mailadres bekend is, ontvang je een herstelmail."]);
        return;
    }

    $token   = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $upd = mysqli_prepare($conn, "UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
    mysqli_stmt_bind_param($upd, 'ssi', $token, $expires, $user['id']);
    mysqli_stmt_execute($upd);

    try {
        $link = "http://localhost:5173/wachtwoord-herstellen?token=" . $token;
        $body = emailTemplate('Wachtwoord herstellen - Tradr', '
            <p style="margin:0 0 8px;font-size:16px;font-weight:700;color:#1a1a1a;">Hallo ' . htmlspecialchars($user['name']) . ',</p>
            <p style="margin:0 0 28px;font-size:15px;color:#555;line-height:1.6;">
                We hebben een verzoek ontvangen om je wachtwoord te herstellen. Klik op de knop hieronder om een nieuw wachtwoord in te stellen.
            </p>

            <div style="text-align:center;margin-bottom:28px;">
                <a href="' . $link . '" style="display:inline-block;background:#2e7d32;color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 36px;border-radius:10px;">
                    Wachtwoord herstellen
                </a>
            </div>

            <p style="margin:0 0 8px;font-size:13px;color:#888;text-align:center;">
                Deze link is <strong>1 uur</strong> geldig.
            </p>
            <p style="margin:0;font-size:13px;color:#aaa;text-align:center;">
                Heb je dit niet aangevraagd? Dan kun je deze e-mail veilig negeren.
            </p>
        ');

        $mail = createMailer();
        $mail->addAddress($email, $user['name']);
        $mail->Subject = 'Wachtwoord herstellen - Tradr';
        $mail->isHTML(true);
        $mail->Body    = $body;
        $mail->AltBody = "Hallo " . $user['name'] . ",\n\nKlik op de volgende link om je wachtwoord te herstellen:\n\n" . $link . "\n\nDeze link is 1 uur geldig.";
        $mail->send();
    } catch (Exception $e) {}

    echo json_encode(["success" => true, "message" => "Als dit e-mailadres bekend is, ontvang je een herstelmail."]);
}
