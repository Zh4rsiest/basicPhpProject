<?php
// You can declare routes here
// Routes can either be post or get method
$router = new Router();
// $router->get("test", "AuthController@test");
$router->post("login", "AuthController@login");
$router->post("logout", "AuthController@logout");

$router->get("user/get", "AuthController@fetchUser", "auth");
$router->get("user/get_by_id", "AuthController@fetchUserById", "auth");
?>
