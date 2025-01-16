<?php

namespace Modules\Telegram;

class TelegramUserAdapter
{
	/**
	 * Conver data to TelegramUser model format
	 *
	 * @return array
	 */
	public static function transform($data): array
	{
		if (isset($data['callback_query'])) {
			$from = $data['callback_query']['from'] ?? [];
			$chat = $data['callback_query']['message']['chat'] ?? [];

			$data = [
				'user_id' => $from['id'] ?? null,
				'username' => $from['username'] ?? null,
				'message' => $data['callback_query']['message']['text'] ?? '',
				'first_name' => $from['first_name'] ?? '',
				'last_name' => $from['last_name'] ?? '',
				'chat_id' => $chat['id'] ?? null,
				'data' => $data['callback_query']['data']
			];
		} else {
			$from = $data['message']['from'] ?? [];
			$chat = $data['message']['chat'] ?? [];

			$data = [
				'user_id' => $from['id'] ?? null,
				'username' => $from['username'] ?? null,
				'message' => $data['message']['text'] ?? '',
				'first_name' => $from['first_name'] ?? '',
				'last_name' => $from['last_name'] ?? '',
				'chat_id' => $chat['id'] ?? null,
				'data' => $data['data']
			];
		}
		return $data;
	}
}
