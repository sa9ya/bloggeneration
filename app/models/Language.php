<?php
namespace App\Models;

use Core\Model;

class Language extends Model {

	public static function getLanguages() {
		//$languages = \App::$app->cache->hmget('languages');
		//if(empty($languages)) {
			$languages = self::find()->all();
			\App::$app->cache->hmset('languages', $languages, 72000);
		//}
		return  $languages;
	}

	public function getLanguageById($language_id) {
		return array_filter(self::getLanguages(), fn($item) => $item['id'] === $language_id)['name'];
	}

}