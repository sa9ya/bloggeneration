<?php

namespace Core;

use App\Models\CronTask;
use App\Models\ProjectCreatedData;
use App\Models\TelegramProject;
use App\Models\TelegramProjectSettings;
use App\Models\TelegramUser;
use App\Models\Language;
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

			$projects = TelegramProject::find()
				->select([
					'tp.id' => 'project_id',
					'tp.name' => 'name',
					'tu.id' => 'user_id',
					'tps.language_id' => 'project_language_id',
					'tps.generator_role' => 'generator_role',
					'tps.generation_text' => 'generation_text',
					'tu.chat_id' => 'chat_id',
					'l.locale' => 'locale',
					'tps.image' => 'generate_image',
				])
				->leftJoin(TelegramProjectSettings::class, ['telegram_project_id' => 'id'])
				->leftJoin(CronTask::class, ['telegram_project_id' => 'id'])
				->leftJoin(TelegramUser::class, ['id' => 'user_id'])
				->leftJoin(Language::class, ['l.id' => 'tps.language_id'])
				->where(['ct.frequency' => $frequency])
				->all(true);

			foreach ($projects as $project) {
				$this->handleRequest($project);
				if($project['generate_image']) {
					sleep(30);
				} else {
					sleep(10);
				}
			}
		} catch (\Exception $e) {
			Logger::error('Error fetching cron requests: ' . $e->getMessage(), $e);
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
		\App::$app->setLanguage($project['locale']);

		[
			'body' => $body,
			'title' => $title,
			'short_text' => $short_text,
			'image_generation_text' => $image_generation_text
		] = $this->openAI->generateArticle($project['generation_text'], $project['generator_role']);

		$projectData = new ProjectCreatedData();
		if($project['generate_image']) {
			$image = new ImageHandler('images/');
			$image->saveImageFromUrl($this->openAI->generateImage($image_generation_text), $project['user_id']);
			$projectData->image = $image->getImageUrl();
		}

		$projectData->language_id = $project['project_language_id'];
		$projectData->project_id = $project['project_id'];
		$projectData->title = $title;
		$projectData->body = $body;
		$projectData->short_text = $short_text;
		$projectData->text_for_image = $image_generation_text;

		if ($projectData->save()) {
			if($project['generate_image']) {
				$this->telegram->sendPhoto($project['chat_id'], $projectData->image, $project['name'] . "\n" . $projectData->short_text);
			}
			$this->telegram->sendMessage($project['chat_id'], $this->telegram->escapeMarkdownV2($projectData->title . "\n" . $projectData->body), 'MarkdownV2');
		}
	}
}