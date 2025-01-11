<?php
namespace App\Controllers;

use App;
use App\Models\TelegramUser;
use App\Models\TelegramUserSettings;
use Core\Controller;
use Core\Logger;
use Modules\Telegram\Telegram;
use Modules\Telegram\TelegramUserAdapter;

class TelegramController extends Controller {
	private Telegram $telegram;

	public function handleWebhook($args): void {
		try {
			$data = TelegramUserAdapter::transform($args);
			$user = TelegramUser::getUser($data['user_id']);

			if(empty($user)) {
				$user = new TelegramUser();
				$user->load($data);
				$user->save();
			} else {
				$user->load($data);
			}

			$this->telegram = new Telegram(App::$app->config->get('telegram')['token']);

			if(!isset($user->telegramUserSettings) || !is_object($user->telegramUserSettings) || empty($user->telegramUserSettings->language_id)) {
				$user->message = '/settings';
			} elseif ($user->status === 0) {
				$this->telegram->sendMessage($user->user_id, "Вам закритий доступ до можливостей цььго бота!\nЗверніться до адміністратора щоб отримати доступ @Isa9yaI");
				exit();
			}

			$this->telegram->loadModel($user);
			$this->telegram->handleRequest();
		} catch (\Exception $e) {
			Logger::error($e->getMessage(), $e->getTrace());
		}
	}

	public function cronTelegram(): void {

	}
}