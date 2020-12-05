<?php

namespace App\Controllers;

use App\Models\Auth as Auth;
use App\Models\User as User;

class AuthController extends BaseController {
  /**
   * Function for logging in
   *
   * @param array $args - an array with email and password values
   * @return mixed
   */
   public function login($args) {
     $args = (object)$args;

     $auth = new Auth();
     $user = $auth->findUserByEmail($args->email);

     $encPassword = password_hash($args->password, PASSWORD_ARGON2I);

     if (password_verify($args->password, $encPassword)) {
       session_start();

       $_SESSION["user"] = (object)[
          "id" => $user->id,
          "name" => $user->name,
          "email" => $user->email,
          "role" => $user->role
       ];

       return $this->json(["success" => true]);
     } else {
       // TODO: Not logged in
       return $this->json(["success" => false]);
     }
   }

   public function logout() {
     session_start();

     if (session_unset() && session_destroy()) {
       return $this->json(["success" => true]);
     }

     return $this->json(["success" => false]);
   }

   public function fetchUser() {
     return Auth::user(true);
   }

   public function fetchUserById($args) {
     $user = new User();
     return $user->find($args->id, true);
   }

   public function getAllUsers() {
     if ($this->isUserLoggedInAndAdmin()) {
       $user = new User();
       return $this->json($user->all());
     }
   }

   public function updateTask($args) {
     if ($this->isUserLoggedInAndAdmin()) {
       $user = new User();
       $args = (object)$args;

       if ($user->updateTask($args)) {
         return $this->json(["success" => true,]);
       }

       return $this->json(["success" => false]);
     }
   }

   private function isUserLoggedInAndAdmin() {
     $user = Auth::user();
     return ($user && $user->role == "admin") ? true : false;
   }
}

?>
