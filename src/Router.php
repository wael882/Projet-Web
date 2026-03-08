<?php

namespace App;

class Router {

    private $routes = [];

    public function add($method, $path, $action) {
        $this->routes[] = [
            'method' => $method,
            'path'   => $path,
            'action' => $action,
        ];
    }

    public function dispatch($method, $uri) {
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $uri) {
                call_user_func($route['action']);
                return;
            }
        }
        http_response_code(404);
        echo '404 - Page non trouvée';
    }
}
