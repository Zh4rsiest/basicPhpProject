<?php

use App\Models\Auth;

class Router {
  /**
   * An array that holds the available routes that can be accessed
   * @var array
   */
  public $routes = [];

  /**
   * Function for calling get and post methods on the class
   *
   * @param string $name - name of the method
   * @param array $args - arguments passed in the method
   *
   * @return false|void
   */
  public function __call($name, $args) {
    // If the invoked method's name is not "get" or "post" then return
    if (!in_array(strtolower($name), ["get", "post"])) {
      return false;
    }

    // Register the route name as an index in the routes array that's value is the
    // controller's and function's name and the type of the route.
    // The syntax for the controller's and function's name is "controller@function"
    // type can only be "get" and "post"
    if (isset($args[2])) {
      $this->routes[$args[0]] = ["controller_function" => $args[1], "type" => $name, "middleware" => $args[2]];
    } else {
      $this->routes[$args[0]] = ["controller_function" => $args[1], "type" => $name];
    }
  }

  /**
   * Function for calling the correct controller's function if it exists. If the
   * route is not registered in the routes array or the controller and/or the function
   * doesn't exist then it returns false or throws an exception
   *
   * @param string $uri - the trimmed requested url
   * @param array $args (optional) - get paramaters passed in the uri
   *
   * @return boolean
   */
  public function resolve($uri) {
    // I needed to somehow access $_GET data as well, so I decided that I will call
    // my get routes with paramaters in the old fashioned style, so I explode
    // the uri from the "?" part. E.g.: /user/get_by_id?id=${id}
    // That way I can access my $_GET variables.
    if (strpos($uri, "?") !== FALSE) {
      $tmp = explode("?", $uri);
      $uri = $tmp[0];
    }

    // If the $uri route exists in the $routes array
    if (array_key_exists(rtrim($uri, "/"), $this->routes)) {
      // Call the "middleware". If it has an "auth" then, then only let logged in users in
      if (isset($this->routes[$uri]["middleware"]) && $this->routes[$uri]["middleware"] == "auth") {
        if (!Auth::user()) {
          throw new Exception("Unauthorized user");
        }
      }

      // If it's a post route, then ignore any routing and let the .htaccess
      // redirect to index.php
      if ($_SERVER["REQUEST_METHOD"] == "GET" && $this->routes[$uri]["type"] == "post") {
        header("location: " . $this->returnHomeAddress());
      }
      // As mentioned above controller and function have this syntax: "controller@function"
      // exploding it into the $tmp variable so it can be called later with the
      // call_user_func_array() function
      $tmp = explode('@', $this->routes[$uri]["controller_function"]);
      $controller = "App\\Controllers\\" . $tmp[0];
      $function = $tmp[1];

      // If the type of the route is get then use the $_GET superglobal, else just
      // use json_decode php://input
      $postObject = (file_get_contents("php://input") === "") ? $_POST : json_decode(file_get_contents("php://input"), true);
      $params = ($this->routes[$uri]["type"] == "get") ? (object)$_GET : $postObject;

      // print_r($params);

      // If the controller and it's funcion exists, then call it and return true
      if (call_user_func_array([new $controller, $function], [$params]) !== FALSE) {
        return true;
      }
      // Else throw exception
      else {
        throw new Exception("Controller and function doesn't exist");
      }
    }
    // If the route doesn't exist then redirect to home
    else {
      // $this->returnHomeAddress();
      return false;
    }
  }

  private function returnHomeAddress() {
    $protocol = explode("/", $_SERVER["SERVER_PROTOCOL"]);
    return $protocol[0] . "://" . $_SERVER["SERVER_NAME"];
  }
}

?>
