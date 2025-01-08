<?php
namespace App\Models;

use Core\Model;
use Core\ModelBase;
use Exception;

class TelegramUser extends Model {

	protected string $table = 'telegram_user';

	public $chat_id;
	public $user_id;
	public int $status = 0;

	/**
	 * @param array $data
	 * @return TelegramUser|null
	 */
	public function getUser($user_id) {
		if(!empty(\App::$app->cache->hgetall('chat' . $user_id))) {
			return (new self)->load(\App::$app->cache->hgetall('chat' . $user_id));
		}

		return self::find()->where(['user_id' => $user_id])->leftJoin(TelegramUserSettings::class, ['user_id' => 'id'])->one();
	}
}