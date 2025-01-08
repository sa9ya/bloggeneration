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
			$this->$key = $value;
		}
		return $this;
	}

	public function loadExistingAttributes(array $data): self {
		foreach ($data as $key => $value) {
			if(property_exists(new static(), $key)) {
				$this->$key = $value;
			}
		}
		return $this;
	}

	protected function hasMatchingAttributes(array $data) {
		foreach ($data as $key => $value) {
			if (property_exists($this, $key) && !empty($value)) {
				return true;
			}
		}
		return false;
	}
}