<?php

namespace App\Controllers;

use App\Models\Auth as Auth;
use App\Models\User as User;

class EmployeeController extends BaseController {
  /**
   * Function for adding new Employee
   *
   * @param array $args - an array with name, email and password values
   * @return mixed
   */
   public function addNewEmployee($args) {
     $user = new User();
     if ($user->create($args)) {
       return $this->json(["success" => true]);
     }

     return $this->json(["success" => false]);
   }
}

?>
