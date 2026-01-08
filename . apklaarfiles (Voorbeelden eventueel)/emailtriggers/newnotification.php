<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';


function sendNewNotification($data, $conn)
{

    $getuserdata = "SELECT * FROM user WHERE userid='" . $data['userid'] . "'";
    mysqli_query($conn, $getuserdata);

    $email = mysqli_fetch_assoc(mysqli_query($conn, $getuserdata))['email'];
    $name = mysqli_fetch_assoc(mysqli_query($conn, $getuserdata))['firstname'];

    $notificationtitle = $data['title'] ?? 'Nieuwe melding';
    $notificationmessage = $data['message'] ?? 'U heeft een nieuwe melding ontvangen.';

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'noreply.apklaar@gmail.com';
        $mail->Password = 'fcivxqefmvmczgvz'; // App wahctwoord
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('noreply.apklaar@gmail.com', 'apklaar');
        $mail->addAddress($email, $name);

        $mail->isHTML(true);
        $mail->Subject = 'Nieuwe melding';

        $mail->Body = "
          <div style='font-family: Arial, sans-serif; margin: 0 auto; padding: 20px; background-color: #f4f4f4;'>
          <div style='background-color: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
              <h2 style='color: #333; margin-top: 0; font-size: 24px;'>apklaar</h2>
              <p style='color: #555; font-size: 16px; line-height: 1.6;'>Hallo <strong>$name</strong>, u heeft een nieuwe melding ontvangen:</p>
              
              <div style='border: 1px solid #b7b8b9ff; border-left: 3px solid #6675fdff; padding: 20px; margin: 20px 0; border-radius: 8px;'>
              <h3 style='margin-top: 0; font-size: 20px;'>$notificationtitle</h3>
              <p style='font-size: 16px; line-height: 1.6; margin-bottom: 0;'>$notificationmessage</p>
              </div>
              
              <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>
              
              <p style='color: #999; font-size: 12px; margin-top: 30px;'>
              Dit is een automatisch gegenereerde melding. Beantwoord alstublieft niet op deze e-mail.
              </p>
          </div>
          </div>
      ";

        $mail->send();
        // echo json_encode(["success" => true, "message" => "Email verstuurd! Controleer uw inbox voor de verificatiecode.<br><a href='verify_code.php'>Ga naar de verificatiepagina</a>"]);
    } catch (Exception) {
        echo json_encode(["success" => false, "message" => "Email versturen is fout gegaan.", "error" => $mail->ErrorInfo], $email);
    }
}




