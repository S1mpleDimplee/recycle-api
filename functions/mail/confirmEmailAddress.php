<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function SendVerificationEmail($data, $conn)
{
    try {
        $to               = $data['email']            ?? '';
        $name             = $data['name']             ?? '';
        $verificationcode = $data['verificationcode'] ?? '';

        if ($conn && $to && $verificationcode) {
            $stmt = mysqli_prepare($conn, "UPDATE users SET verification_code = ? WHERE email = ?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'ss', $verificationcode, $to);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }

        $body = emailTemplate('Account bevestigen - Tradr', '
            <p style="margin:0 0 8px;font-size:16px;font-weight:700;color:#1a1a1a;">Hallo ' . htmlspecialchars($name) . ',</p>
            <p style="margin:0 0 24px;font-size:15px;color:#555;line-height:1.6;">
                Welkom bij Tradr! Voer de onderstaande code in om je account te activeren.
            </p>

            <div style="background:#e8f5e9;border:2px solid #2e7d32;border-radius:12px;padding:24px;text-align:center;margin-bottom:24px;">
                <p style="margin:0 0 4px;font-size:12px;font-weight:600;color:#2e7d32;text-transform:uppercase;letter-spacing:1px;">Verificatiecode</p>
                <p style="margin:0;font-size:42px;font-weight:800;color:#2e7d32;letter-spacing:10px;">' . htmlspecialchars($verificationcode) . '</p>
            </div>

            <p style="margin:0 0 20px;font-size:13px;color:#888;text-align:center;">
                Deze code is 24 uur geldig.
            </p>

            <p style="margin:0;font-size:13px;color:#aaa;text-align:center;">
                Heb je geen account aangemaakt? Dan kun je deze e-mail negeren.
            </p>
        ');

        $mail = createMailer();
        $mail->addAddress($to, $name);
        $mail->Subject  = 'Je verificatiecode - Tradr';
        $mail->isHTML(true);
        $mail->Body     = $body;
        $mail->AltBody  = "Hallo $name,\n\nJe verificatiecode is: $verificationcode\n\nDeze code is 24 uur geldig.";
        $mail->send();

        echo json_encode(["success" => true, "message" => "E-mail verstuurd! Controleer je inbox voor de verificatiecode."]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "E-mail versturen mislukt.", "error" => $e->getMessage()]);
    }
}

function confirmEmailWithLink($data, $conn)
{
    $email            = $data['email']            ?? '';
    $verificationcode = $data['verificationcode'] ?? '';

    if (empty($email) || empty($verificationcode)) {
        echo json_encode(["success" => false, "message" => "Ongeldige code. Probeer opnieuw."]);
        return;
    }

    $stmt = mysqli_prepare($conn, "SELECT id, verification_code FROM users WHERE email = ?");
    if (!$stmt) {
        echo json_encode(["success" => false, "message" => "Er is iets misgegaan. Probeer het later opnieuw."]);
        return;
    }
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$user) {
        echo json_encode(["success" => false, "message" => "Geen account gevonden voor dit e-mailadres."]);
        return;
    }

    if (($user['verification_code'] ?? '') !== $verificationcode) {
        echo json_encode(["success" => false, "message" => "Ongeldige of verlopen code. Vraag een nieuwe verificatiecode aan."]);
        return;
    }

    $updateStmt = mysqli_prepare($conn, "UPDATE users SET email_verified = 1, verification_code = NULL WHERE email = ?");
    if (!$updateStmt) {
        echo json_encode(["success" => false, "message" => "Bevestigen mislukt. Probeer het later opnieuw."]);
        return;
    }
    mysqli_stmt_bind_param($updateStmt, 's', $email);
    $updated = mysqli_stmt_execute($updateStmt);
    mysqli_stmt_close($updateStmt);

    if ($updated) {
        echo json_encode(["success" => true, "message" => "E-mailadres bevestigd. Je kunt nu inloggen."]);
    } else {
        echo json_encode(["success" => false, "message" => "Bevestigen mislukt. Probeer het later opnieuw."]);
    }
}
