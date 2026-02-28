<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';


function SendVerificationEmail($data)
{
    try 
    {
        $to = $data['email'] ?? '';
        $name = $data['name'] ?? '';
        $verificationcode = $data['verificationcode'] ?? '';
        $subject = 'Welkom bij sparesort';
        $from = " sparesortbali@jaylanovanderveen.nl";
        $password = "school123!@#";

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
?>