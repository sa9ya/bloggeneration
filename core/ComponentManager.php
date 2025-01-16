<?php

namespace Core;

class ComponentManager
{
	protected array $components = [];

	public function __construct(array $components)
	{
		foreach ($components as $name => $component) {
			$this->components[$name] = $component['class'];
		}
	}

	public function __get($name)
	{
		return $this->components[$name] ?? null;
	}
}