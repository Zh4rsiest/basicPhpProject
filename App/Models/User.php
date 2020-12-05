<?php

namespace App\Models;

use App\Controllers\MailController;

class User extends BaseModel {
  /**
   * Length of the user's random password
   * @var int
   */
  const PASSWORD_LENGTH = 7;

  /**
   * An array that holds what tasks had the employee been through already
   * @var array
   */
  public $tasks;


  public function __construct() {
    parent::__construct();
  }

  /**
   * Function for creating user
   *
   * @param array $user - an array with name and email values
   * @return mixed
   */
  public function create($user) {
    $sql = "INSERT INTO users(name, email, password, role_id) VALUES(?, ?, ?, ?)";
    $user = (object)$user;

    $length = isset($user->password_length) ? $user->password_length : self::PASSWORD_LENGTH;
    $password = $this->generateRandomPassword($length);

    $subject = "Your new account is ready " . $user->name;
    $body = "
      <h3>Dear " . $user->name . ",</h3>
      <p>
        We welcome you at Blexr! Your authentication credentials are:
        <br><br>
        <b>Username:</b> " . $user->email . "<br>
        <b>Password:</b> " . $password . "<br>
        <br>
        Wish you a good day!
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
      $this->errorLog("Couldn't send email to user");
      return false;
    }

    $encPassword = password_hash($password, PASSWORD_ARGON2I);

    if (!($stmt = $this->mysqli->prepare($sql))) {
     $this->errorLog("Prepare failed: (" . $this->mysqli->errno . ") " . $this->mysqli->error);
     return false;
   }

    if (!$stmt->bind_param("sssi", $user->name, $user->email, $encPassword, $user->role)) {
      $this->errorLog("Binding parameters failed: (" . $stmt->errno . ")" . $stmt->error);
      return false;
    }

    if (!$stmt->execute()) {
      $this->errorLog("Execute failed: (" . $stmt->errno . ")" . $stmt->error);
      return false;
    }

    $this->createUserTask($stmt->insert_id);
    return true;
    // return $stmt;
  }

  /**
  * Create a users_tasks record in the database where the users onboarding tasks can be followed
  *
  * @param int $id - id of the created user
  *
  * @return bool
  */
  private function createUserTask($id) {
    // No need to prepare the statement as it was returned by the insert query
    $sql = "INSERT INTO users_tasks(user_id, microsoft_office_license, email_access, git_repository, jira_access) VALUES($id, 0, 0, 0, 0)";

    if (!$this->mysqli->query($sql)) {
      $this->errorLog("Query failed: (" . $this->mysqli->errno . ")" . $this->mysqli->error);
      return false;
    }

    // $this->log("users_tasks record created");
    return true;
  }

  /**
   * Generate a random password for the user
   *
   * @param int (optional) length - how long the password should be
   *
   * @return string
   */
  private function generateRandomPassword($length = self::PASSWORD_LENGTH) {
    $haystack = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $pass = "";

    // Generate a password matching the PASSWORD_LENGTH constant or $length
    // parameter and make every second character lowercase
    for ($i=0; $i < $length; $i++) {
      $char = substr($haystack, rand(0, strlen($haystack)), 1);
      $pass .= ($i % 2 == 0) ? strtolower($char) : $char;
    }

    return $pass;
  }

  /**
   * Return all instances of the model
   *
   * @return mixed
   */
  public function all() {
    $sql = "SELECT * FROM $this->table";
    $result = $this->mysqli->query($sql);

    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        // Append tasks to user
        $tasks = $this->tasks($row["id"]);
        $row["tasks"] = ($tasks) ? $tasks : [];

        $arr[] = $row;
      }

      return $arr;
    }

    $this->errorLog("There are no records for the model");
  }

  /**
   * Returns the progression of tasks of the user
   *
   * @param int $userId - Id of the user
   *
   * @return mixed
   */
  private function tasks($userId) {
    $sql = "SELECT microsoft_office_license, email_access, git_repository, jira_access FROM users_tasks WHERE user_id = $userId";
    $result = $this->mysqli->query($sql);

    if ($result->num_rows > 0) {
      return $result->fetch_assoc();
    }

    return false;
  }

  /**
   * Updates a user's task list
   *
   * @param int $userId - Id of the user
   *
   * @return boolean
   */
  public function updateTask($args) {
    $sql = "UPDATE users_tasks SET " . $args->task . " = ? WHERE user_id = ?";

    if (!($stmt = $this->mysqli->prepare($sql))) {
     $this->errorLog("Prepare failed: (" . $this->mysqli->errno . ") " . $this->mysqli->error);
     return false;
   }

    if (!$stmt->bind_param("ii", $args->value, $args->user_id)) {
      $this->errorLog("Binding parameters failed: (" . $stmt->errno . ")" . $stmt->error);
      return false;
    }

    if (!$stmt->execute()) {
      $this->errorLog("Execute failed: (" . $stmt->errno . ")" . $stmt->error);
      return false;
    }

    // TODO: Megnézni az összes $stmt-t
    // $stmt->close();
    return true;
  }

  /**
   * Fetch a user by Id
   *
   * @param int $id - id of the user
   * @param boolean $jsonEncoded (optional) - wether to return it as json or not
   */
  public function find($id, $jsonEncoded = false) {
    $sql = "SELECT a.id, a.name, a.email, b.name as role FROM users a JOIN roles b ON (a.role_id = b.id) WHERE a.id = ?";

    if (!($stmt = $this->mysqli->prepare($sql))) {
     $this->errorLog("Prepare failed: (" . $this->mysqli->errno . ") " . $this->mysqli->error);
     return false;
   }

    if (!$stmt->bind_param("i", $id)) {
      $this->errorLog("Binding parameters failed: (" . $stmt->errno . ")" . $stmt->error);
      return false;
    }

    if (!$stmt->execute()) {
      $this->errorLog("Execute failed: (" . $stmt->errno . ")" . $stmt->error);
      return false;
    }

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
      $user = $result->fetch_object();
      $user->tasks = $this->tasks($user->id);
      $stmt->close();

      if ($jsonEncoded) {
        echo json_encode($user);
      } else {
        return $user;
      }

      return $user;
    } else {
      $this->errorLog("User not found");
      return false;
    }
  }
}
?>
