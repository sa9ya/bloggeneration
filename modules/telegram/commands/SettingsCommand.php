<?php
namespace Modules\Telegram\Commands;

use App\Models\Language;
use App\Models\TelegramUserSettings;
use Core\Logger;
use Modules\Telegram\Command;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class SettingsCommand extends Command {


	protected bool $hidden = true;
	protected array $stepArray = [
		'save_language' => "set_language_",
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
		if (!$this->getUserId()) {
			Logger::error('Chat ID not found for settings command.');
			return;
		}

		if (!$this->checkStep($this->userModel->data)) {
			$this->telegram->sendMessage($this->getUserId(), "Невірні дані.");
			exit;
		}

		switch ($this->getStep()) {
			case 'save_language':
				$userSettings = new TelegramUserSettings();
				$userSettings->language_id = substr($this->userModel->data, strlen('set_language_'));
				$userSettings->telegram_user_id = $this->userModel->id;
				if ($userSettings->save()) {
					$this->settingDone($userSettings);
				}
				break;
			default:
				$keyboard = new InlineKeyboardMarkup([$this->generateLanguageMenu()]);

				$this->telegram->sendMessage($this->getUserId(), "Оберіть мову:", null, false, null, $keyboard);
				$this->setStep( 'save_language');
				break;
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

	private function settingDone($userSettings) {
			$language = Language::getLanguageById($userSettings->language_id);
			$this->removeStep();
			$this->telegram->sendMessage($this->getUserId(), "Аккаунт налаштовано! Ваші налаштування:\n
				Мова - " . $language['name']);
	}
}