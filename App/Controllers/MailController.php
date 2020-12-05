<?php

namespace App\Controllers;

use App\Models\Mail;

Class MailController extends BaseController {
  /**
   * Function for sending e-mail to an email address with name, subject and body
   *
   * @param object $args - An object with to_email, to_name, subject, body and optional json_response, from_email, from_name members
   * @return void|Exception
   */
  public function sendMail($args) {
    $mail = new Mail();

    if ($mail->sendMail($args)) {
      return (isset($args->json_response)) ? $this->json(["success" => true]) : true;
    }

    return (isset($args->json_response)) ? $this->json(["success" => false]) : false;
  }
}

?>
