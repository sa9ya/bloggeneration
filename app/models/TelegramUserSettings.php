<?php
namespace App\Models;

use Core\Model;

class TelegramUserSettings extends Model {
	protected string $table = 'telegram_user_settings';

	public $user_id;
	public $language_id;

	public static function getUserSettings($user_id) {
		return self::find()->where(['user_id' => $user_id])->one();
	}
}