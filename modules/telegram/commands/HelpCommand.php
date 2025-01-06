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

	public function execute(): void {
		$commands = $this->registry->getCommands();

		$message = "Доступні команди:\n";
		foreach ($commands as $command) {
			if ($command->isHidden()) {
				continue;
			}
			$message .= "{$command->getName()} - {$command->getDescription()}\n";
		}
		$this->telegram->sendMessage($this->userModel->chat_id, $message);
	}
}