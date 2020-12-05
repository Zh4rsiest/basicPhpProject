<?php

namespace App\Controllers;

use App\Models\Auth;
use App\Models\User;
use App\Models\Request;
use App\Controllers\MailController;

use DateTime;

class RequestController extends BaseController {
  /**
   * Function for adding a new request
   *
   * @param array $args - an array with the date and hours
   * @return mixed
   */
   public function addNewRequest($args) {
     // Check at backend as well if the user tries to make a request less than 4
     // hours before the end of the previous day
     $hour = new DateTime();
     $date = new DateTime();

     if ($hour->format("H") >= 20 && $args["date"] === $date->modify('+1 day')->format("Y-m-d")) {
       return $this->json(["success" => false, "message" => "You can only make a request 4 hours before the end of the previous days"]);
     }

     $request = new Request();
     // Return if the user already has request for that date;
     if ($request->getDateAndUser($args)) {
       return $this->json(["success" => false, "message" => "You already have a request for that date. If you wish to change the hours, delete it and make a new request"]);
     }

     if ($request->create($args)) {
       return $this->json(["success" => true]);
     }

     return $this->json(["success" => false]);
   }

   /**
    * Function for adding a new request
    *
    * @return mixed
    */
    public function fetchLoggedInUsersRequests() {
      $request = new Request();

      if (($requests = $request->requestsByUserId(Auth::user()->id))) {
        return $this->json(["success" => true, "requests" => $requests]);
      }

      return $this->json(["success" => false, "requests" => []]);
    }

   /**
    * Function for getting all requests with employee details
    *
    * @return mixed
    */
    public function fetchAllRequestsWithEmployee() {
      $request = new Request();

      if (is_array($requests = $request->allWithEmployee())) {
        return $this->json(["success" => true, "requests" => $requests]);
      }

      return $this->json(["success" => false, "requests" => []]);
    }

    /**
     * Function for adding a new request
     *
     * @param array $args - an array with the date and hours
     * @return mixed
     */
    public function deleteById($args) {
      $request = new Request();
      $args = (object)$args;

      if (Auth::user() !== false && ($requests = $request->deleteById($args->id))) {
       return $this->json(["success" => true]);
      }

      return $this->json(["success" => false]);
    }

   /**
    * Function for processing a request
    *
    * @param array $args - an array with the date and hours
    * @return mixed
    */
    public function processRequest($args) {
      $request = new Request();
      $args = (object)$args;

      // Return if the update failed
      if ($request->update(["status" => $args->status], $args->id)) {
        $userModel = new User();
        $user = $userModel->find($args->user_id);
        $status = ($args->status == 1) ? "Approved" : "Declined";

        $subject = "Your request has been processed";
        $body = "
          <h3>Dear " . $user->name . ",</h3>
          <p>
            There's an update on your request
            <br><br>
            <b>Date:</b> " . $args->date . "<br>
            <b>Status:</b> " . $status . "<br>
          </p>
        ";

        // Create a mail object with values according to the controller
        $mail = (object)[
          "to_name" => $user->name,
          "to_email" => $user->email,
          "subject" => $subject,
          "body" => $body
        ];
        $mailController = new MailController();

        if (!$mailController->sendMail($mail)) {
          return $this->json(["success" => false, "message" => "There was a problem with sending email"]);
        }
        return $this->json(["success" => true]);
      }

      return $this->json(["success" => false]);
    }
}

?>
