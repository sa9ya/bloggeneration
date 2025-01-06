<?php

namespace Core\Services;

use Core\Base;
use Exception;

class OpenAIService extends Base {
	private string $apiKey;
	private string $apiBase;

	public function __construct() {
		parent::__construct();

		$this->apiKey = App::$app->config->get('openai')['api_key'] ?? '';
		$this->apiBase = App::$app->config->get('openai')['api_base'] ?? 'https://api.openai.com/v1';

		if (empty($this->apiKey)) {
			throw new Exception('API Key for OpenAI is not set in the configuration.');
		}
	}

	/**
	 * Generate post body
	 *
	 * @param string $topic
	 * @param array $options
	 * @return string
	 * @throws Exception
	 */
	public function generateArticle(string $topic, array $options = []): string {
		$endpoint = "{$this->apiBase}/chat/completions";

		$defaultOptions = [
			'model' => 'gpt-4',
			'max_tokens' => 1000
		];
		$options = array_merge($defaultOptions, $options);

		$payload = [
			'model' => $options['model'],
			'messages' => [
				['role' => 'system', 'content' => 'You are a professional content writer.'],
				['role' => 'user', 'content' => "Write a detailed article about: $topic"]
			],
			'max_tokens' => $options['max_tokens']
		];

		$response = $this->sendRequest($endpoint, $payload);

		if (empty($response['choices'][0]['message']['content'])) {
			throw new Exception("Failed to generate article for topic: $topic");
		}

		return $response['choices'][0]['message']['content'];
	}

	/**
	 * Generate Image
	 *
	 * @param string $description
	 * @param array $options
	 * @return string URL of image
	 * @throws Exception
	 */
	public function generateImage(string $description, array $options = []): string {
		$endpoint = "{$this->apiBase}/images/generations";

		$defaultOptions = [
			'size' => '1024x1024',
			'n' => 1
		];
		$options = array_merge($defaultOptions, $options);

		$payload = [
			'prompt' => $description,
			'n' => $options['n'],
			'size' => $options['size']
		];

		$response = $this->sendRequest($endpoint, $payload);

		if (empty($response['data'][0]['url'])) {
			throw new Exception("Failed to generate image for description: $description");
		}

		return $response['data'][0]['url'];
	}

	/**
	 * Send request to open AI
	 *
	 * @param string $endpoint
	 * @param array $payload
	 * @return array
	 * @throws Exception
	 */
	private function sendRequest(string $endpoint, array $payload): array {
		$ch = curl_init($endpoint);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
			"Authorization: Bearer {$this->apiKey}"
		]);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

		$response = curl_exec($ch);

		if (curl_errno($ch)) {
			throw new Exception('Request Error: ' . curl_error($ch));
		}

		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($httpCode < 200 || $httpCode >= 300) {
			throw new Exception("API Error: Received HTTP Code $httpCode");
		}

		curl_close($ch);

		$decodedResponse = json_decode($response, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			throw new Exception("JSON Decode Error: " . json_last_error_msg());
		}

		return $decodedResponse;
	}
}