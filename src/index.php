<?php
// Inclut le fichier homeController.php, qui contient la définition de la classe homeController.
require_once __DIR__ . '/controller/homeController.php';
require_once __DIR__ . '/controller/registerController.php';
require_once __DIR__ . '/controller/loginController.php';

// Inclut le fichier createDatabase.php, qui contient la logique pour créer et configurer la base de données.
require_once __DIR__ . '/database/createDatabase.php';

// Définition des routes de l'application. Chaque route est associée à un contrôleur et une méthode spécifiques.
$routes = [
    '/' => ['controller' => 'home\homeController', 'method' => 'home'],
    '/register' => ['controller' => 'register\registerController', 'method' => 'register'],
    '/login' => ['controller' => 'login\loginController', 'method' => 'login'],
];

// Récupère l'URL demandée par le client et la divise en deux parties: le chemin et les paramètres de requête (s'il y en a).
$requestParts = explode('?', $_SERVER['REQUEST_URI'], 2);
$path = $requestParts[0]; // Le chemin de la requête (la partie avant le '?' s'il y en a un).

// Vérifie si le chemin de la requête existe dans le tableau des routes définies.
if (array_key_exists($path, $routes)) {
  // Récupère le nom du contrôleur et de la méthode associés à la route demandée.
  $controllerName = $routes[$path]['controller'];
  $methodName = $routes[$path]['method'];

  // Crée une instance du contrôleur.
  $controller = new $controllerName();

  // Récupère les paramètres de requête s'il y en a.
  $params = isset($requestParts[1]) ? $requestParts[1] : '';

  // Appelle la méthode du contrôleur.
  $controller->$methodName();
}
