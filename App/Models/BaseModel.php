<?php

namespace App\Models;

use DateTime;
use mysqli;
use App\Models\Auth;

class BaseModel {
  public $mysqli;
  protected $table;

  public function __construct() {
    $className = explode('\\', get_class($this));
    $this->table = strtolower(array_pop($className)) . "s";
    // Wrap the connection in a try-catch block to hijack errors
    try {
      $this->mysqli = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    } finally {
      // $this->log("Database connection established<br>");
    }
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
          $arr[] = $row;
      }

      return $arr;
    }

    $this->errorLog("There are no records for the model");
  }

  /**
   * Delete a record by id
   *
   * @param int $id - id of the record
   * @return mixed
   */
  public function deleteById($id) {
    $sql = "DELETE FROM $this->table WHERE id = $id";
    $result = $this->mysqli->query($sql);

    if ($result) {
      return true;
    }

    $this->errorLog("Couldn't delete record");
    return false;
  }

  /**
   * Update a record with arguments by id
   *
   * @param int $id - id of the record
   * @return mixed
   */
  public function update($args, $id) {
    $update = "";
    // Concat all to be updated columns with their values to a string
    foreach ($args as $key => $value) {
      $update .= "SET $key = $value AND";
    }
    // Cut the last AND from the $update string
    $update = rtrim($update, " AND");
    // If there were no parameters at all, then return false
    if (strlen($update) === 0 || $update == "") {
      $this->errorLog("There are no update parameters");
      return false;
    }
    // Execute the query
    $sql = "UPDATE $this->table " . $update . " WHERE id = $id";
    $result = $this->mysqli->query($sql);

    if ($result) {
      return true;
    }

    $this->errorLog("Couldn't delete record");
    return false;
  }

  /**
   * Function for logging errors to the page if DEBUG_MODE constant is true
   *
   * @param string $message - The message that will be written to the page
   * @return void
   */
  public function errorLog($message) {
    if (DEBUG_MODE === true) echo "<p style='color: red;>'" . $message . "</p>";
  }

  /**
   * Function for logging text to screen if DEBUG_MODE constant is true
   *
   * @param string $message - The message that will be written to the page
   * @return void
   */
  public function log($message) {
    $date = new DateTime();
    $date = $date->format("y:m:d h:i:s:u");

    if (DEBUG_MODE === true) echo "<p>[" . $date . "]: " . $message . "</p>";
  }
}
?>
