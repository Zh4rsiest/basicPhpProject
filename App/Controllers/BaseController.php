<?php

namespace App\Controllers;

Class BaseController {
  public function json($value) {
    echo json_encode($value);
  }
}

?>
