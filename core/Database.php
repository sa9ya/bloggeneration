<?php

namespace Core;

use App;
use Modules\Telegram\Command;
use PDO;
use Exception;

class Database
{
	private static ?PDO $pdo = null;

	public static function getConnection(): PDO
	{
		if (self::$pdo === null) {
			try {
				self::$pdo = new PDO(
					'mysql:host=' . App::$app->config->get('db')['host'] . ';dbname=' . App::$app->config->get('db')['name'] . ';charset=utf8',
					App::$app->config->get('db')['user'],
					App::$app->config->get('db')['pass']
				);
				self::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
				self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
			} catch (\PDOException $e) {
				Logger::error('Database connection failed', $e);
				exit('Database connection error.');
			}
		}
		return self::$pdo;
	}
}