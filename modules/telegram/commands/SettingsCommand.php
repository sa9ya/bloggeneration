<?php
namespace Modules\Telegram\Commands;

use App\Models\Language;
use App\Models\TelegramUserSettings;
use Core\Logger;
use Modules\Telegram\Command;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class SettingsCommand extends Command {

	private array $stepArray = [
		1 => "set_language_",
	];

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
			Logger::error('Chat ID not found for settings command.');
			return;
		}
		$this->step = \App::$app->cache->get('user_' . $chat_id . '_step') ?? 0;
		Logger::error('lang', "data " . json_encode($this->userModel));

		if (!$this->isStepCorrect($this->userModel->data)) {
			$this->telegram->sendMessage($chat_id, "Невірні дані. Будь ласка користуйтесь кнопками під останнім записом");
			exit;
		}

		switch ($this->step) {
			case 0:
				$keyboard = new InlineKeyboardMarkup([$this->generateLanguageMenu()]);

				$this->telegram->sendMessage($chat_id, "Оберіть мову:", null, false, null, $keyboard);
				\App::$app->cache->set('user_' . $chat_id . '_step', 1);
				break;
			case 1:
				$userSettings = new TelegramUserSettings();
				$userSettings->language_id = substr($this->userModel->data, strlen('set_language_'));
				$userSettings->telegram_user_id = $this->userModel->id;
				$userSettings->save();
				break;
		}

		if($this->step) {
			$language = Language::getLanguageById($userSettings->language_id);
			\App::$app->cache->del('user_' . $chat_id . '_step');
			$this->telegram->sendMessage($chat_id, "Аккаунт налаштовано! Ваші налаштування:\n
				Мова - " . $language['name']);
		}
	}

	private function generateLanguageMenu(): array
	{
		$languages = Language::getLanguages();
		$buttons = [];

		foreach ($languages as $language) {
			$buttons[] = [
				'text' => $language['name'],
				'callback_data' => 'set_language_' . $language['id']
			];
		}

		return $buttons;
	}

	private function isStepCorrect($data) {
		return str_contains($data, $this->stepArray[$this->step]);
	}
}