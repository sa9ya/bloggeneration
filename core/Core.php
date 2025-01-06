<?php
namespace Core;

class Core {

	/**
	 * Magic isset for dynamic attributes
	 *
	 * @param string $key Attribute name
	 * @return bool
	 */
	public function __isset(string $key): bool {
		return isset($this->{$key});
	}

	/**
	 * Set attribute
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function __set(string $key, $value): void {
		$this->attributes[$key] = $value;
	}

	public function __get($key) {
		return $this->attributes[$key] ?? null;
	}
}