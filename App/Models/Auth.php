<?php

namespace App\Models;

class Auth extends BaseModel {
  public function __construct() {
    parent::__construct();
  }

  /**
   * Function for creating user
   *
   * @param array $user - an array with name and email values
   * @return mixed
   */
  public static function user($jsonEncoded = false) {
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }

    if (isset($_SESSION["user"]) && $_SESSION["user"]->name != null) {
      if ($jsonEncoded)
        echo json_encode($_SESSION["user"]);
      else
        return $_SESSION["user"];
    } else {
      if ($jsonEncoded) {
        echo 'false';
      } else
        return false;
    }
  }

  public function findUserByEmail($email) {
    $sql = "SELECT a.id, a.name, a.email, b.name as role FROM users a JOIN roles b ON (a.role_id = b.id) WHERE email = ?";

    if (!($stmt = $this->mysqli->prepare($sql))) {
     $this->errorLog("Prepare failed: (" . $this->mysqli->errno . ") " . $this->mysqli->error);
     return false;
   }

    if (!$stmt->bind_param("s", $email)) {
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
      $stmt->close();

      return $user;
    } else {
      // TODO: Return something
      $this->errorLog("User not found");
    }

  }
}
?>
