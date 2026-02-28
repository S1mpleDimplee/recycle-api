<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';


function SendVerificationEmail($data, $conn)
{
    try 
    {
        $to = $data['email'] ?? '';
        $name = $data['name'] ?? '';
        $verificationcode = $data['verificationcode'] ?? '';
        $subject = 'Welkom bij sparesort';
        $from = " sparesortbali@jaylanovanderveen.nl";
        $password = "school123!@#";

        // Sla verificatiecode op voor bevestiging via link (kolom verification_code toevoegen: ALTER TABLE user ADD verification_code VARCHAR(10) DEFAULT NULL;)
        if ($conn && $to && $verificationcode) {
            $stmt = mysqli_prepare($conn, "UPDATE user SET verification_code = ? WHERE email = ?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'ss', $verificationcode, $to);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }

        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';
    
        $body = 
        "Beste " . $name . ",\n\n" .
        "Bedankt voor uw registratie bij sparesort.\n"
        . "Klik op de volgende link om uw email adres te bevestigen:\n\n" .
        "http://localhost:3000/verificatie?verificationcode=" . $verificationcode . "&email=" . urlencode($to) . "\n\n" .
        "Met vriendelijke groet,\n";

        $mail->IsSMTP();
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->SMTPDebug = 0;
        $mail->SMTPAuth = true;
        $mail->Host = 'smtp.jaylanovanderveen.nl';
        $mail->Port = 587;

        $mail->Username = $from;
        $mail->Password = $password;

        $mail->setFrom($from, 'sparesort');
        $mail->addAddress($to, $name);
        $mail->Subject = $subject;
        $mail->Body = $body;

        if (!$mail->send()) 
        {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        } 
        else 
        {
            echo json_encode(["success" => true, "message" => "Email verstuurd! Controleer uw inbox voor de verificatielink."]);
        }
    } 
    catch (Exception $e)
    {
        echo json_encode(["success" => false, "message" => "Email versturen is fout gegaan.", "error" => $e->getMessage()]);
    }
}

function confirmEmailWithLink($data, $conn)
{
    $email = $data['email'] ?? '';
    $verificationcode = $data['verificationcode'] ?? '';

    if (empty($email) || empty($verificationcode)) {
        echo json_encode(["success" => false, "message" => "Ongeldige link. Gebruik de link uit de e-mail."]);
        return;
    }

    $stmt = mysqli_prepare($conn, "SELECT id, verification_code FROM user WHERE email = ?");
    if (!$stmt) {
        echo json_encode(["success" => false, "message" => "Er is iets misgegaan. Probeer het later opnieuw."]);
        return;
    }
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$user) {
        echo json_encode(["success" => false, "message" => "Geen account gevonden voor dit e-mailadres."]);
        return;
    }

    if (($user['verification_code'] ?? '') !== $verificationcode) {
        echo json_encode(["success" => false, "message" => "Ongeldige of verlopen link. Vraag een nieuwe verificatielink aan."]);
        return;
    }

    $updateStmt = mysqli_prepare($conn, "UPDATE user SET email_verified = 1, verification_code = NULL WHERE email = ?");
    if (!$updateStmt) {
        echo json_encode(["success" => false, "message" => "Bevestigen mislukt. Probeer het later opnieuw."]);
        return;
    }
    mysqli_stmt_bind_param($updateStmt, 's', $email);
    $updated = mysqli_stmt_execute($updateStmt);
    mysqli_stmt_close($updateStmt);

    if ($updated) {
        echo json_encode(["success" => true, "message" => "Uw e-mailadres is bevestigd. U kunt nu inloggen."]);
    } else {
        echo json_encode(["success" => false, "message" => "Bevestigen mislukt. Probeer het later opnieuw."]);
    }
}
?>