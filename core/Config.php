<?php
namespace Core;

class Config {
	private static ?Config $instance = null;
	private array $settings;

	private function __construct() {
		$this->settings = require __DIR__ . '/../config/config.php';
	}

	private function __clone() {}
	private function __wakeup() {}

	public static function getInstance(): Config {
		if (self::$instance === null) {
			self::$instance = new Config();
		}
		return self::$instance;
	}

	public function get(string $key) {
		return $this->settings[$key] ?? null;
	}
}