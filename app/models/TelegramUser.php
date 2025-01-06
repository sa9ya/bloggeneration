<?php
namespace App\Models;

use Core\Model;
use Core\ModelBase;
use Exception;

class TelegramUser extends Model {

	protected string $table = 'telegram_user';

	public int $status = 0;

	/**
	 * @param array $data
	 * @return TelegramUser|null
	 */
	public static function getUser(array $data) {
		if(!empty(\App::$app->cache->hgetall('chat' . $data['user_id']))) {
			return (new self)->load(\App::$app->cache->hgetall('chat' . $data['user_id']));
		}
		$user = self::find()->load($data)->where(['user_id' => $data['user_id']])->one();
		\App::$app->cache->hMSet('chat' . $data['user_id'], $user->toArray());
		return $user;
	}
}