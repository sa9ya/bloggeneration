<?php

namespace Modules\Telegram\Commands;

use Core\Logger;
use Modules\Telegram\Command;

class CreateCommand extends Command
{
	public function getName(): string
	{
		return '/create';
	}

	public function getDescription(): string
	{
		return 'Створення і налаштування проекту.';
	}

	public function execute(): void
	{
		if (!$this->getUserId()) {
			Logger::error('Chat ID not found for settings command.');
			return;
		}

		if (!$this->isStepCorrect($this->userModel->data)) {
			$this->telegram->sendMessage($this->getUserId(), "Невірні дані. Будь ласка спробуйте ще раз і дотримуйтесь інструкцій.");
			exit;
		}
	}
}
