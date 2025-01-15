<?php

namespace Modules\Telegram\Commands;

use Core\Logger;
use Modules\Telegram\Command;

class HelpCommand extends Command {

	public function getName(): string {
		return '/help';
	}

	public function getDescription(): string {
		return 'Список доступних команд.';
	}

	public function getText() {
		$commands = $this->registry->getCommands();

		$message = "Доступні команди:\n";
		foreach ($commands as $command) {
			if ($command->isHidden()) {
				continue;
			}
			$message .= "{$command->getName()} - {$command->getDescription()}\n";
		}
		return $message;
	}

	public function execute(): void {
		$response = $this->telegram->sendMessage($this->userModel->user_id, $this->getText());
		Logger::info('test', $response);
	}
}