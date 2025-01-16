<?php

namespace App\Models;

use Core\Model;

class Language extends Model
{

	public $id;
	public $name;
	public $url;

	public static function getLanguages()
	{
//		$languages = json_decode(\App::$app->cache->get('languages'), true);
//		if(empty($languages)) {
		$languages = self::find()->all(true);
//			\App::$app->cache->set('languages', json_encode($languages));
//		}
		return $languages;
	}

	public static function getLanguageById($language_id)
	{
		$language = array_filter(self::getLanguages(), function ($language) use ($language_id) {
			return $language['id'] == $language_id;
		});
		return reset($language);
	}
}