<?php
namespace Core;

class RequestHandler
{
	private array $requestData;

	public function __construct()
	{
		$this->requestData = $this->parseInput();
	}

	private function parseInput(): array
	{
		$rawInput = file_get_contents('php://input');
		return json_decode($rawInput, true) ?? [];
	}

	public function getRequestData(): array
	{
		return $this->requestData;
	}

	public function get(string $key, $default = null)
	{
		return $this->requestData[$key] ?? $default;
	}
}