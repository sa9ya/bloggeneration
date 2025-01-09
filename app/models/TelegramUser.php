<?php
namespace App\Models;

use Core\Model;
use Core\ModelBase;
use Exception;

class TelegramUser extends Model {

	protected string $table = 'telegram_user';

	public $id;
	public int $status = 1;

	/**
	 * @param array $data
	 * @return TelegramUser|null
	 */
	public static function getUser($user_id) {
		if(!empty(\App::$app->cache->hgetall('chat' . $user_id))) {
			return (new self)->load(\App::$app->cache->hgetall('chat' . $user_id));
		}

		$user = self::find()->where(['user_id' => $user_id])->leftJoin(TelegramUserSettings::class, ['user_id' => 'id'])->one();
		if(!empty($user)) {
			\App::$app->cache->hmset('chat' . $user_id, $user->toArray(), 72000);
		}

		return $user;
	}
}