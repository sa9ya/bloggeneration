<?php

namespace Core;

use App\Models\CronTask;
use App\Models\ProjectCreatedData;
use App\Models\TelegramProject;
use App\Models\TelegramProjectSettings;
use App\Models\TelegramUser;
use Core\Services\Image\ImageHandler;
use Core\Services\Openai\OpenAIService;
use DateTime;
use Modules\Telegram\Telegram;

class CronHandler
{
	private OpenAIService $openAI;
	private Telegram $telegram;

	public function __construct()
	{
		$this->openAI = new OpenAIService();
		$this->telegram = new Telegram(\App::$app->config->get('telegram')['token']);
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

			$projects = TelegramProject::find()
				->leftJoin(TelegramProjectSettings::class, ['telegram_project_id' => 'id'])
				->leftJoin(CronTask::class, ['telegram_project_id' => 'id'])
				->leftJoin(TelegramUser::class, ['id' => 'user_id'])
				->where(['ct.frequency' => $frequency, 'tps.telegram_project_id IS NOT NULL'])
				->all(true);
			foreach ($projects as $project) {
				$this->handleRequest($project);
			}
		} catch (\Exception $e) {
			Logger::error('Error fetching cron requests: ' . $e->getMessage());
		}
	}

	/**
	 * Handle a single cron request.
	 *
	 * @param array $request The cron request data.
	 * @throws \Exception If processing fails.
	 */
	private function handleRequest(array $project): void
	{
		[
			'body' => $body,
			'title' => $title,
			'short_text' => $short_text,
			'image_generation_text' => $image_generation_text
		] = $this->openAI->generateArticle($project['generation_text'], $project['generator_role']);

		$image = new ImageHandler('images/');
		$image->saveImageFromUrl($this->openAI->generateImage($image_generation_text), $project['id']);

		$projectData = new ProjectCreatedData();
		$projectData->language_id = $project['language_id'];
		$projectData->project_id = $project['telegram_project_id'];
		$projectData->image = $image->getImageUrl();
		$projectData->title = $title;
		$projectData->body = $body;
		$projectData->short_text = $short_text;
		$projectData->text_for_image = $image_generation_text;

		if ($projectData->save()) {
			$this->telegram->sendPhoto($project['chat_id'], $projectData->image, $this->telegram->escapeMarkdownV2(\App::$app->language->get('New post for:') . "\n" . $project['name'] . $projectData->short_text));
			$this->telegram->sendMessage($project['chat_id'], $this->telegram->escapeMarkdownV2($projectData->title . "\n\n" . $projectData->body), 'MarkdownV2');
		}
	}
}