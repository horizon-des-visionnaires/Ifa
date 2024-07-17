<?php
require_once __DIR__ . '/controller/homeController.php';
require_once __DIR__ . '/controller/registerController.php';
require_once __DIR__ . '/controller/loginController.php';
require_once __DIR__ . '/controller/profileController.php';
require_once __DIR__ . '/controller/postDetailsController.php';
require_once __DIR__ . '/controller/dashboardController.php';

require_once __DIR__ . '/database/createDatabase.php';

$routes = [
  '/' => ['controller' => 'home\homeController', 'method' => 'home'],
  '/register' => ['controller' => 'register\registerController', 'method' => 'register'],
  '/login' => ['controller' => 'login\loginController', 'method' => 'login'],
  '/dashboard' => ['controller' => 'dashboard\dashboardController', 'method' => 'dashboard'],
];

$requestParts = explode('?', $_SERVER['REQUEST_URI'], 2);
$path = $requestParts[0];

if (array_key_exists($path, $routes)) {
  $controllerName = $routes[$path]['controller'];
  $methodName = $routes[$path]['method'];

  $controller = new $controllerName();

  $params = isset($requestParts[1]) ? $requestParts[1] : '';

  $controller->$methodName();
} else {
  if (preg_match('/^\/profile-(\d+)$/', $path, $matches)) {
    $controller = new profile\profileController();
    $controller->profile($matches[1]);
  } else if (preg_match('/^\/postDetails-(\d+)$/', $path, $matches)) {
    $controller = new postDetails\postDetailsController();
    $controller->post($matches[1]);
  } else {
    http_response_code(404);
    echo "Page not found";
  }
}
