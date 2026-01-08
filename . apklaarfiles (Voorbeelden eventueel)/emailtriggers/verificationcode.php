<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';


function SendVerificationEmail($data)
{
  $code = rand(100000, 999999);
  $_SESSION['verification_code'] = $code;

  $to = $data['email'] ?? '';
  $name = $data['name'] ?? '';

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
    $mail->addAddress($to, $name);

    $mail->isHTML(true);
    $mail->Subject = 'Jou verificatiecode';

    $mail->Body = "
      <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f4f4f4;'>
          <div style='background-color: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
              <h2 style='color: #333; margin-top: 0; font-size: 24px;'>apklaar</h2>
              <p style='color: #555; font-size: 16px; line-height: 1.6;'>Hallo <strong>$name</strong>,</p>
              <p style='color: #555; font-size: 16px; line-height: 1.6;'>Hier is uw verificatiecode:</p>
              
              <div style='background-color: #007bff; color: #ffffff; font-size: 32px; font-weight: bold; text-align: center; padding: 20px; margin: 20px 0; border-radius: 8px; letter-spacing: 5px;'>
                  $code
              </div>
              
              <p style='color: #555; font-size: 16px; line-height: 1.6;'>Voer deze code in op de verificatiepagina om uw identiteit te bevestigen.</p>
              
              <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>
              
              <p style='color: #999; font-size: 14px;'>Deze code vervalt over 5 minuten.</p>
              
              <p style='color: #999; font-size: 12px; margin-top: 30px;'>
                  Als u deze verificatie niet heeft aangevraagd, negeer dan deze e-mail.
              </p>
          </div>
      </div>
      ";

    $mail->send();
    echo json_encode(["success" => true, "message" => "Email verstuurd! Controleer uw inbox voor de verificatiecode.<br><a href='verify_code.php'>Ga naar de verificatiepagina</a>"]);
  } catch (Exception) {
    echo json_encode(["success" => false, "message" => "Email versturen is fout gegaan.", "error" => $mail->ErrorInfo]);
  }
}


function CheckIfCodeIsValid($data, $conn)
{
  $inputCode = $data['code'] ?? '';
  $userid = $data['userid'] ?? null;

  if (!$userid) {
    echo json_encode(["success" => false, "message" => "Ongeldige gebruiker."]);
    return;
  }

  if (isset($_SESSION['verification_code']) && $_SESSION['verification_code'] == $inputCode) {
    unset($_SESSION['verification_code']);
    echo json_encode(["success" => true, "message" => "Verificatie succesvol!"]);

    $updateUserVerifieStatusSQL = "UPDATE user SET isverified = 1 WHERE id = '$userid'";
    mysqli_query($conn, $updateUserVerifieStatusSQL);

    AddNotification([
      "userid" => $userid,
      "preset" => "verfication_success"
    ], $conn);

  } else {
    echo json_encode(["success" => false, "message" => "Ongeldige verificatiecode, Probeer het opnieuw."]);
  }
}




