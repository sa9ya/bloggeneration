<?php

namespace Core\Services\Openai;

use Exception;

class OpenAIService
{
	private string $apiKey;
	private string $apiBase;

	private array $instagramOptions = [
		'model' => 'gpt-4o',
		'n' => 3,
		'max_tokens' => 1500,
		'size' => '1024x1024',
		'temperature' => 0.7
	];

	public function __construct()
	{
		$this->apiKey = \App::$app->config->get('open_ai')['api_key'];
		$this->apiBase = \App::$app->config->get('open_ai')['api_base'];

		if (empty($this->apiKey)) {
			throw new Exception('API Key for OpenAI is not set in the configuration.');
		}
	}

	/**
	 * Generate post body
	 *
	 * @param string $topic
	 * @param array $options
	 *
	 * @throws Exception
	 */
	public function generateArticle(string $topic, string $gpt_role = null, array $options = [])
	{
		if (empty($topic)) {
			throw new Exception("Topic cant be empty");
		}

		$endpoint = "{$this->apiBase}/chat/completions";

		$options = array_merge($this->instagramOptions, $options);

		$payload = [
			'model' => $options['model'],
			'messages' => [
				['role' => 'system', 'content' => $gpt_role ?? \App::$app->language->get('You are a professional content writer.')],
				['role' => 'user', 'content' => \App::$app->language->get("Write a detailed article about: ") . $topic]
			],
			"response_format" => [
				"type" => "json_schema",
				"json_schema" => [
					"name" => "article_structure",
					"schema" => [
						"type" => "object",
						"required" => [
							"body",
							"title",
							"short_text",
							"image_generation_text"
						],
						"properties" => [
							"body" => [
								"type" => "string",
								"description" => \App::$app->language->get('Body of the article. Must contain close to 200 words and hash tags in the end')
							],
							"title" => [
								"type" => "string",
								"description" => \App::$app->language->get('Title of the article. Must be close to 10 words')
							],
							"short_text" => [
								"type" => "string",
								"description" => \App::$app->language->get('Short text which be placed on the photo of the article. Without any special characters, must be up to 7 words')
							],
							"image_generation_text" => [
								"type" => "string",
								"description" => \App::$app->language->get('Short text about this article for image generation')
							]
						],
						"additionalProperties" => false
					],
					"strict" => true
				]
			],
			'temperature' => $options['temperature'],
			'max_tokens' => $options['max_tokens'],
		];

		$response = $this->sendRequest($endpoint, $payload);

		if (empty($response['choices'][0]['message']['content'])) {
			throw new Exception("Failed to generate article for topic: $topic");
		}

		return json_decode($response['choices'][0]['message']['content'], true);
	}

	/**
	 * Generate Image
	 *
	 * @param string $description
	 * @param array $options
	 * @return string URL of image
	 * @throws Exception
	 */
	public function generateImage(string $description, array $options = []): string
	{
		$endpoint = "{$this->apiBase}/images/generations";

		$options = array_merge($this->instagramOptions, $options);

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
	private function sendRequest(string $endpoint, array $payload): array
	{
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