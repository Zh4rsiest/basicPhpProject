<?php

namespace App\Models;

use App\Models\Auth;

class Request extends BaseModel {
  public function __construct() {
    parent::__construct();
  }

  /**
   * Function for creating a new request by the logged in user
   *
   * @param array $args - an array with the date and hours
   * @return boolean
   */
  public function create($args) {
    $args = (object)$args;
    $sql = "INSERT INTO $this->table(user_id, date, hours) VALUES(?, ?, ?)";

    if (!($stmt = $this->mysqli->prepare($sql))) {
     $this->errorLog("Prepare failed: (" . $this->mysqli->errno . ") " . $this->mysqli->error);
     return false;
   }

    if (!$stmt->bind_param("isi", Auth::user()->id, $args->date, $args->hours)) {
      $this->errorLog("Binding parameters failed: (" . $stmt->errno . ")" . $stmt->error);
      return false;
    }

    if (!$stmt->execute()) {
      $this->errorLog("Execute failed: (" . $stmt->errno . ")" . $stmt->error);
      return false;
    }

    return true;
  }

  /**
   * Return all instances of the model
   *
   * @return mixed
   */
  public function allWithEmployee() {
    $sql = "SELECT * FROM $this->table";
    $result = $this->mysqli->query($sql);

    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $employee = $this->employee($row["user_id"]);
        $row["employee"] = $employee;

        $arr[] = $row;
      }

      return $arr;
    }

    $this->errorLog("There are no records for the model");
    return false;
  }

  /**
   * Returns the employee's data
   *
   * @param int $userId - id of the user
   * @return mixed
   */
  private function employee($userId) {
    $sql = "SELECT id, name, email FROM users WHERE id = $userId";
    $result = $this->mysqli->query($sql);

    if ($result->num_rows > 0) {
      return $result->fetch_assoc();
    }

    return false;
  }

  /**
   * Function for checking if the user already has a request for the date
   *
   * @param array $args - an array with the date and hours
   * @return boolean
   */
  public function getDateAndUser($args) {
    $args = (object)$args;
    $sql = "SELECT * FROM $this->table WHERE user_id = ? AND date = ?";

    if (!($stmt = $this->mysqli->prepare($sql))) {
     $this->errorLog("Prepare failed: (" . $this->mysqli->errno . ") " . $this->mysqli->error);
     return false;
   }

    if (!$stmt->bind_param("is", Auth::user()->id, $args->date)) {
      $this->errorLog("Binding parameters failed: (" . $stmt->errno . ")" . $stmt->error);
      return false;
    }

    if (!$stmt->execute()) {
      $this->errorLog("Execute failed: (" . $stmt->errno . ")" . $stmt->error);
      return false;
    }

    $result = $stmt->get_result();

    if ($result->num_rows >= 1) {
      return true;
    }

    return false;
  }

  /**
   * Returns the requests made by the user
   *
   * @param int $userId - Id of the user
   *
   * @return mixed
   */
  public function requestsByUserId($userId) {
    $sql = "SELECT id, date, hours, status FROM requests WHERE user_id = $userId ORDER BY date ASC";
    $result = $this->mysqli->query($sql);

    if ($result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {
        $arr[] = $row;
      }

      return $arr;
    }

    return false;
  }
}
?>
