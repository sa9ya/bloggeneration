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
			$user = TelegramUser::getUser($data);

			if(empty($user)) {
				$user->load($data);
				$user->save();
			}
			$this->telegram = new Telegram(App::$app->config->get('telegram')['token']);
			$user->userSettings = TelegramUserSettings::getUserSettings($user->id);
			$this->telegram->loadModel($user);

			if($user->status === 0) {
				$this->telegram->sendMessage($data['chat_id'], "Вам закритий доступ до можливостей цььго бота!\nЗверніться до адміністратора щоб отримати доступ @Isa9yaI");
				exit();
			}

			$user->message = "/update_settings";
			if(empty($user->userSettings)) {
			}

			$this->telegram->handleRequest();
		} catch (\Exception $e) {
			Logger::error($e->getMessage(), $e->getTrace());
		}
	}

	public function cronTelegram($args): void {

	}
}