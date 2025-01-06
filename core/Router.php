<?php
namespace Core;

class Router {
	private $routes;

	public function __construct() {
		$this->routes = require __DIR__ . '/../config/routes.php';
	}

	public function dispatch() {
		$url = $_GET['url'] ?? '';
		$url = trim($url, '/');

		if ($this->isApiRoute($url)) {
			$this->handleRoute($url, 'api');
		} else {
			$this->handleRoute($url, 'web');
		}
	}

	private function isApiRoute($url) {
		return strpos($url, 'api/') === 0;
	}

	private function handleRoute($url, $type) {
		$routes = $this->routes[$type] ?? [];

		if (!isset($routes[$url])) {
			http_response_code(404);
			Logger::error('Route ' . $url . ' not found.');
			die();
		}

		$controllerName = 'App\\Controllers\\' . $routes[$url]['controller'];
		$methodName = $routes[$url]['method'];

		if (!class_exists($controllerName)) {
			http_response_code(500);
			Logger::error('Controller ' . $controllerName . ' not found.');
			die();
		}

		$controller = new $controllerName();

		if (!method_exists($controller, $methodName)) {
			http_response_code(500);
			Logger::error('Method ' . $methodName . ' not found in controller ' . $controllerName . '.');
			die();
		}

		$reflection = new \ReflectionMethod($controller, $methodName);
		$params = $reflection->getParameters();

		$requestHandler = new RequestHandler();
		if (count($params) > 0 && $params[0]->allowsNull()) {
			$controller->$methodName($requestHandler->getRequestData());
		} elseif (count($params) > 0) {
			$controller->$methodName($requestHandler->getRequestData());
		} else {
			$controller->$methodName();
		}
	}
}