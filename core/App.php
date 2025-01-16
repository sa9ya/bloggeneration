<?php

use Core\Config;
use Core\Language;

class App
{
	public static $instance = null;
	public static $app;

	protected function __construct()
	{
		$this->initializeComponents();
		$this->config = Config::getInstance();
		$this->language = Language::getInstance();
		self::$app = $this;
	}

	public function __get($name)
	{
		return $this->$name ?? null;
	}

	public function __set($name, $value)
	{
		$this->$name = $value;
	}

	public static function getInstance()
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function initializeComponents()
	{
		foreach ((require __DIR__ . '/../config/config.php')['components'] as $name => $component) {
			$this->$name = $component['class'];
		}
	}

	public function setLanguage(string $lang) {
		$this->language = Language::getInstance($lang);
	}
}
