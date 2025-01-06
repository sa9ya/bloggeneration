<?php
namespace Modules\Telegram;

class TelegramUserAdapter {
	/**
	 * Conver data to TelegramUser model format
	 *
	 * @return array
	 */
	public static function transform($data): array {
		$from = $data['message']['from'] ?? [];
		$chat = $data['message']['chat'] ?? [];

		return [
			'user_id' => $from['id'] ?? null,
			'username' => $from['username'] ?? null,
			'message' => $data['message']['text'] ?? '',
			'first_name' => $from['first_name'] ?? '',
			'last_name' => $from['last_name'] ?? '',
			'chat_id' => $chat['id'] ?? null,
			'status' => 0,
			'data' => $data['data']
		];
	}
}
