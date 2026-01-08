<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';


function sendCarDeletedEmail($data, $conn)
{

    $getuserdata = "SELECT * FROM user WHERE userid='" . $data['userid'] . "'";
    mysqli_query($conn, $getuserdata);

    $email = mysqli_fetch_assoc(mysqli_query($conn, $getuserdata))['email'];
    $name = mysqli_fetch_assoc(mysqli_query($conn, $getuserdata))['firstname'];

    $carname = $data['carname'] ?? 'Onbekende auto';

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
        $mail->Subject = 'Auto verwijderd';

        $mail->Body = "
          <div style='font-family: Arial, sans-serif; margin: 0 auto; padding: 20px; background-color: #f4f4f4;'>
          <div style='background-color: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
              <h2 style='color: #333; margin-top: 0; font-size: 24px;'>apklaar</h2>
              <p style='color: #555; font-size: 16px; line-height: 1.6;'>Hallo <strong>$name</strong>, er is een auto verwijderd!</p>
              
              <div style='border-left: 3px solid #b65f0eff; padding: 20px; margin: 20px 0; border-radius: 8px;'>
              <h3 style='margin-top: 0; font-size: 20px;'>De auto $carname is verwijderd van uw account om " . date('d-m-Y H:i') . "</h3>
              <p style='font-size: 16px; line-height: 1.6; margin-bottom: 0;'>Bent u dit niet geweest neem direct contact op met onze klanten service</p>
              </div>
              
              <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>
              
              <p style='color: #999; font-size: 12px; margin-top: 30px;'>
              Dit is een automatisch gegenereerde melding. Beantwoord alstublieft niet op deze e-mail.
              </p>
          </div>
          </div>
      ";

        $mail->send();
    } catch (Exception) {
        echo json_encode(["success" => false, "message" => "Email versturen is fout gegaan.", "error" => $mail->ErrorInfo], $email);
    }
}




