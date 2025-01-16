<?php

namespace Core\Services\Image;

class ImageHandler
{
	private string $baseStoragePath = 'images/';
	private string $filePath = '';

	/**
	 * Save an image from a URL to local storage.
	 *
	 * @param string $imageUrl The URL of the image.
	 * @param int $userId The ID of the user.
	 * @return string The unique filename of the saved image.
	 * @throws \Exception If the image cannot be downloaded or saved.
	 */
	public function saveImageFromUrl(string $imageUrl, int $userId = 0): string
	{
		$userStoragePath = $this->getUserStoragePath($userId);

		// Get the image data
		$imageData = file_get_contents($imageUrl);
		if ($imageData === false) {
			throw new \Exception("Unable to download image from URL: $imageUrl");
		}

		// Determine the extension from the URL
		$extension = pathinfo($imageUrl, PATHINFO_EXTENSION);
		if (!$extension) {
			throw new \Exception("Unable to determine the image extension from URL: $imageUrl");
		}

		// Generate a unique filename
		$filename = $this->generateUniqueFilename($extension);

		// Ensure the filename is unique in the directory
		$filename = $this->ensureUniqueFilename($userStoragePath, $filename);

		// Save the image to local storage
		$this->filePath = $userStoragePath . $filename;
		if (file_put_contents($this->filePath, $imageData) === false) {
			throw new \Exception("Unable to save image to: $this->filePath");
		}

		return $filename;
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