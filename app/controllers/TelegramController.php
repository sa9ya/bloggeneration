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
			$user = new TelegramUser();
			$data = TelegramUserAdapter::transform($args);
			$user = $user->getUser($data['user_id']);
			$user->load($data);

			if(empty($user)) {
				$user->save();
			}

			$this->telegram = new Telegram(App::$app->config->get('telegram')['token']);
			$this->telegram->loadModel($user);

			if(!isset($user->telegramUserSettings) && !is_object($user->telegramUserSettings)) {

				$this->telegram->handleRequest();
				exit();
			}


			if($user->status === 0) {
				$this->telegram->sendMessage($user->chat_id, "Вам закритий доступ до можливостей цььго бота!\nЗверніться до адміністратора щоб отримати доступ @Isa9yaI");
				exit();
			}

			$this->telegram->handleRequest();
		} catch (\Exception $e) {
			Logger::error($e->getMessage(), $e->getTrace());
		}
	}

	public function cronTelegram($args): void {

	}
}