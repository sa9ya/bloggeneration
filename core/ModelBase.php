<?php
namespace Core;

class ModelBase extends Core {

	/**
	 * Load data into model
	 *
	 * @param array $data Array in format ['key' => 'value'].
	 * @return $this Return current object
	 */
	public function load(array $data): self {
		foreach ($data as $key => $value) {
			$this->attributes[$key] = $value;
		}
		return $this;
	}
}