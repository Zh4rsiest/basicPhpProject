<?php
require("vendor/autoload.php");
require("routes/web.php");
use App\Models\Auth as Auth;

$uri = ltrim($_SERVER["REQUEST_URI"], "/");

if ($uri != "" && $router->resolve($uri, $_GET)) {
  exit;
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Blexr HR app</title>
    <link rel="stylesheet" href="/public/css/main.css"/>
    <script type="text/javascript">
      window.user = <?php Auth::user(true) ?>;
    </script>
  </head>
  <body>
    <div id="app">
      <navbar></navbar>
      <router-view></router-view>
    </div>
    <script src="/public/js/app.js"></script>
  </body>
</html>
