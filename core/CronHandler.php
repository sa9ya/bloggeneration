<?php

namespace Core;

use App\Models\CronTask;
use App\Models\TelegramProject;
use App\Models\TelegramProjectSettings;
use DateTime;

class CronHandler {

	public function __construct()
	{

	}

	/**
	 * Process cron requests from the database.
	 */
	public function processRequests(): void
	{
		try {
			$now = new DateTime();
			$day = $now->format('j');
			$hour = $now->format('G');

			$frequency = "*/" . $hour;
			var_dump($frequency);

			$data = TelegramProject::find()
				->leftJoin(TelegramProjectSettings::class, ['telegram_project_id' => 'id'])
				->leftJoin(CronTask::class, ['telegram_project_id' => 'id'])
				->where(['ct.frequency' => $frequency, 'tps.telegram_project_id IS NOT NULL'])
				->all(true);
			var_dump($data);
		} catch (\Exception $e) {
			$this->logger->error('Error fetching cron requests: ' . $e->getMessage());
		}
	}

	/**
	 * Handle a single cron request.
	 *
	 * @param array $request The cron request data.
	 * @throws \Exception If processing fails.
	 */
	private function handleRequest(array $request): void
	{
		$this->logger->info('Processing request ID: ' . $request['id']);

		switch ($request['type']) {
			case 'generate_text':
				$this->generateText($request);
				break;

			case 'send_notification':
				$this->sendNotification($request);
				break;

			default:
				throw new \InvalidArgumentException('Unknown request type: ' . $request['type']);
		}

		$this->databaseService->markRequestAsCompleted($request['id']);
	}

	/**
	 * Generate text (example operation).
	 *
	 * @param array $request The request data.
	 */
	private function generateText(array $request): void
	{
		$this->logger->info('Generating text for request ID: ' . $request['id']);
	}

	/**
	 * Send a notification (example operation).
	 *
	 * @param array $request The request data.
	 */
	private function sendNotification(array $request): void
	{
		// Add logic to send a notification
		$this->logger->info('Sending notification for request ID: ' . $request['id']);
	}
}