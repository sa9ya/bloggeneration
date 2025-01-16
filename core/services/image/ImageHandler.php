<?php

namespace Core\Services\Image;

class ImageHandler
{
	private string $baseStoragePath = 'images/';
	private string $filePath = '';

	/**
	 * Save an image from a URL with query parameters to local storage.
	 *
	 * @param string $imageUrl The full URL of the image, including query parameters.
	 * @param int $userId The ID of the user.
	 * @return string The unique filename of the saved image.
	 * @throws \Exception If the image cannot be downloaded or saved.
	 */
	public function saveImageFromUrl(string $imageUrl, int $userId): string
	{
		$userStoragePath = $this->getUserStoragePath($userId);

		// Download the image using cURL
		$imageData = $this->downloadImage($imageUrl);

		// Determine the extension from the URL or default to 'png'
		$extension = $this->getImageExtension($imageUrl);

		// Generate a unique filename
		$filename = $this->generateUniqueFilename($extension);

		// Save the image to local storage
		$this->filePath = $userStoragePath . $filename;
		if (file_put_contents($this->filePath, $imageData) === false) {
			throw new \Exception("Unable to save image to: $this->filePath");
		}

		return $filename;
	}

	/**
	 * Determine the file extension of an image from its URL.
	 *
	 * @param string $imageUrl The URL of the image.
	 * @return string The file extension (e.g., 'png', 'jpg').
	 */
	private function getImageExtension(string $imageUrl): string
	{
		$pathInfo = pathinfo(parse_url($imageUrl, PHP_URL_PATH));

		return $pathInfo['extension'] ?? 'png';
	}

	/**
	 * Generate a unique filename for an image.
	 *
	 * @param string $extension The file extension.
	 * @return string The unique filename.
	 */
	private function generateUniqueFilename(string $extension): string
	{
		return uniqid('image_', true) . '.' . $extension;
	}

	/**
	 * Ensure the filename is unique in the storage directory.
	 * If a file with the same name exists, a new name is generated.
	 *
	 * @param string $storagePath The directory to check.
	 * @param string $filename The original filename.
	 * @return string A unique filename.
	 */
	private function ensureUniqueFilename(string $storagePath, string $filename): string
	{
		$filePath = $storagePath . $filename;
		while (file_exists($filePath)) {
			$filename = $this->generateUniqueFilename(pathinfo($filename, PATHINFO_EXTENSION));
			$filePath = $storagePath . $filename;
		}
		return $filename;
	}

	/**
	 * Get the storage path for a specific user.
	 *
	 * @param int $userId The ID of the user.
	 * @return string The user's storage path.
	 */
	private function getUserStoragePath(int $userId): string
	{
		$encodedUserId = $this->encodeUserId($userId);
		$userStoragePath = $this->baseStoragePath . ($encodedUserId ? "user_$encodedUserId/" : 'all_images/');
		if (!is_dir($userStoragePath)) {
			if (!mkdir($userStoragePath, 0755, true) && !is_dir($userStoragePath)) {
				throw new \RuntimeException(sprintf('Directory "%s" was not created', $userStoragePath));
			}
		}
		return $userStoragePath;
	}

	/**
	 * Download image data from a URL using cURL.
	 *
	 * @param string $imageUrl The URL of the image.
	 * @return string The binary data of the image.
	 * @throws \Exception If the image cannot be downloaded.
	 */
	private function downloadImage(string $imageUrl): string
	{
		$ch = curl_init($imageUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

		$imageData = curl_exec($ch);

		if (curl_errno($ch)) {
			throw new \Exception('cURL Error: ' . curl_error($ch));
		}

		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($httpCode !== 200) {
			throw new \Exception("Failed to download image, HTTP Status Code: $httpCode");
		}

		curl_close($ch);

		return $imageData;
	}

	/**
	 * Encode the user ID to ensure it is safe for use in file paths.
	 *
	 * @param int $userId The user ID to encode.
	 * @return string The encoded user ID.
	 */
	private function encodeUserId(int $userId): string
	{
		return base64_encode((string)$userId);
	}

	/**
	 * Get the full path of a stored image by filename.
	 *
	 * @param int $userId The ID of the user.
	 * @param string $filename The filename of the image.
	 * @return string The full path to the image.
	 */
	public function getImageUrl(): string
	{
		return \App::$app->config->get('project_url') . $this->filePath;
	}
}