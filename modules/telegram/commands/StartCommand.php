<?php

namespace Modules\Telegram\Commands;

use Modules\Telegram\Command;

class StartCommand extends Command
{
	protected bool $hidden = true;

	public function getName(): string
	{
		return '/start';
	}

	public function getDescription(): string
	{
		return 'Початок роботи з ботом.';
	}

	public function execute(): void
	{
		$full_name = ($this->userModel->first_name . ' ' . $this->userModel->last_name) ?? '';
		$this->telegram->sendMessage($this->userModel->user_id, "Ласкаво просимо до бота " . $full_name . "!\n
		Використовуйте /help, щоб побачити доступні команди.\n
		Для початку роботи потрібно пройти етап налаштування.");
	}
}
