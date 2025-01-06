<?php

return [
	'db' => [
		'host' => 'DB_HOST_NAME',
		'name' => 'DB_NAME',
		'user' => 'DB_USER_NAME',
		'pass' => 'DB_PASSWORD',
		'table_prefix' => 'DB_TABLE_PREFIX',
	],
	'telegram' => [
		'token' => 'YOUR_TELEGRAM_TOCKEN'
	],
	'open_ai' => [
		'api_key' => 'YOUR_OPEN_AI_TOCKEN',
		'api_base' => 'https://api.openai.com/v1/'
	],
	'components' => [
		'cache' => [
			'class' => new Predis\Client('REDIS_SOCKET')
		]
	]
];