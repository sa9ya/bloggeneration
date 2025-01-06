<?php
namespace Core;

class ComponentManager {
	protected array $components = [];

	public function __construct(array $components) {
		foreach ($components as $name => $component) {
			$this->components[$name] = $component['class'];
		}
	}

//	protected function initializeComponent(array $component) {
//		$class = $component['class'];
//		$type = $component['type'] ?? '';
//		$config = $component['config'] ?? [];
//		return new $class($config);
//	}

	public function __get($name) {
		return $this->components[$name] ?? null;
	}
}