<?php
namespace Modules\Telegram\Commands;

use App\Models\Language;
use App\Models\TelegramUserSettings;
use Core\Logger;
use Modules\Telegram\Command;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class SettingsCommand extends Command {

	public function getName(): string
	{
		return "/settings";
	}

	public function getDescription(): string
	{
		return \App::$app->language->get('setting_command_description');
	}

	public function execute(): void
	{
		$chat_id = $this->userModel->user_id;

		if (!$chat_id) {
			\App::$app->logger->error('Chat ID not found for settings command.');
			return;
		}
		$this->step = \App::$app->cache->get('user_' . $chat_id . '_step') ?? 0;
		Logger::error('asd', $chat_id . " asddddd " . $this->step);

		switch ($this->step) {
			case 0:
				$languages = Language::getLanguages();
				$keyboard = new InlineKeyboardMarkup([[
					[
						'text' => "eng",
						'callback_data' => 'set_language_1'
					],
					[
						'text' => "ukr",
						'callback_data' => 'set_language_2'
					]
				]]);

				$this->telegram->sendMessage($chat_id, "Оберіть мову:", null, false, null, $keyboard);
				\App::$app->cache->set('user_' . $chat_id . '_step', 1);
				break;
			case 1:
				$userSettings = new TelegramUserSettings();
				$userSettings->language_id = substr($this->userModel->data, strlen('set_language_'));
				$userSettings->user_id = $this->userModel->id;
				Logger::error('asd setings ', json_encode($userSettings->toArray()));
				$language = Language::getLanguageById($userSettings->language_id);
				Logger::error('asd', json_encode($language));

				$userSettings->save();
				$this->telegram->sendMessage($chat_id, "Аккаунт налаштовано! Ваші налаштування:\n
				Мова - " . $language['name']);
			default:
				\App::$app->cache->del('user_' . $chat_id . '_step');
		}
		Logger::error('asd', "asddddd stp " . $this->step);
	}

	private function generateLanguageMenu(array $languages): array
	{
		$buttons = [];

		foreach ($languages as $language) {
			$buttons[] = [
				[
					'text' => $language['name'],
					'callback_data' => 'set_language_' . $language['id']
				]
			];
		}

		return $buttons;
	}
}