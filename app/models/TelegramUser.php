<?php

namespace App\Models;

use Core\Model;
use Core\ModelBase;
use Exception;

class TelegramUser extends Model
{

	protected string $table = 'telegram_user';

	public $data;

	/**
	 * @param array $data
	 * @return TelegramUser|null
	 */
	public static function getUser($user_id)
	{
		// $user = \App::$app->cache->hgetall('chat' . $user_id);
//		if(empty($user)) {
		$user = self::find()->where(['user_id' => $user_id])->leftJoin(TelegramUserSettings::class, ['telegram_user_id' => 'id'])->one();
//			\App::$app->cache->hmset('chat' . $user_id, $user->toArray(), 72000);
//			return (new self)->load(\App::$app->cache->hgetall('chat' . $user_id));
//		}

		if (!empty($user) && isset($user->telegramUserSettings->language_id)) {
			\App::$app->language = \App::$app->language->getInstance(Language::getLanguageById($user->telegramUserSettings->language_id)['locale']);
		}

		return $user;
	}
}