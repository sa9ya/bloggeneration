<?php
namespace Modules\Telegram\Commands;

use App\Models\Language;
use App\Models\TelegramUserSettings;
use Core\Logger;
use Modules\Telegram\Command;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class UpdateSettingsCommand extends Command {

	protected bool $hidden = true;
	protected int $step = 0;

	public function getName(): string {
		return '/update_settings';
	}

	public function getDescription(): string {
		return '';
	}

	public function execute(): void
	{
		$chat_id = $this->userModel->user_id;

		if (!$chat_id) {
			Logger::error('Chat ID not found for settings command.');
			return;
		}
		//$this->step = \App::$app->cache->get('user_' . $chat_id . '_step') ?? 0;

		switch ($this->step) {
			case 0:
				$languages = Language::getLanguages();
				$keyboard = new InlineKeyboardMarkup($this->generateLanguageMenu($languages), true);

				$this->telegram->sendMessage($chat_id, "Оберіть мову:", null, false, null, $keyboard);
				//\App::$app->cache->set('user_' . $chat_id . '_step', 1);
				break;
			case 1:
				$languages = Language::getLanguages();

				$userSettings = new TelegramUserSettings();
				$userSettings->language_id = substr($this->userModel->data, strlen('set_language_'));
				$userSettings->save();
				$this->telegram->sendMessage($chat_id, "Аккаунт налаштовано! Ваші налаштування:\n
				Мова - " . $languages->getLanguageById($userSettings->language_id));
				break;
			default:
				$this->telegram->sendMessage($chat_id, "Невідомий етап. Почніть налаштування знову.");
				$this->step = 0;
		}
	}
}