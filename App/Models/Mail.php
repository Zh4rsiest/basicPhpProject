<?php

namespace App\Models;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mail extends BaseModel {
  /**
   * Function for sending e-mail to an email address with name, subject and body
   *
   * @param object $args - An object with to_email, to_name, subject, body and optional from_email, from_name members
   * @return void|Exception
   */
  public function sendMail($args) {
    // Instantiation and passing "true" enables exceptions
    $mail = new PHPMailer(true);

    try {
      //Server settings
      // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
      $mail->isSMTP();                                            // Send using SMTP
      $mail->Host       = MAIL_HOST;                    // Set the SMTP server to send through
      $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
      $mail->Username   = MAIL_USERNAME;                     // SMTP username
      $mail->Password   = MAIL_PASSWORD;                               // SMTP password
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
      $mail->Port       = MAIL_PORT;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

      $fromEmail = $args->from_email ?? MAIL_DEFAULT_FROM_EMAIL;
      $fromName = $args->from_name ?? MAIL_DEFAULT_FROM_NAME;

      //Recipients
      $mail->setFrom($fromEmail, $fromName);
      $mail->addAddress($args->to_email, $args->to_name);     // Add a recipient

      // Content
      $mail->isHTML(true);                                  // Set email format to HTML
      $mail->Subject = $args->subject;
      $mail->Body    = $args->body;
      // $mail->AltBody = "This is the body in plain text for non-HTML mail clients";

      if ($mail->send()) {
        return true;
      }

      return false;
    } catch (Exception $e) {
      echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
  }
}
?>
