<?php
namespace Modules\Telegram;

use Core\Logger;

class CommandRegistry {
	private array $commands = [];

	public function register(Command $command): void {
		$this->commands[$command->getName()] = $command;
	}

	public function getCommands(): array {
		return $this->commands;
	}

	public function handle(string $commandName): void {
		if (isset($this->commands[$commandName])) {
			try {
				$this->commands[$commandName]->execute();
			} catch (\Exception $e) {
				Logger::error($e->getMessage(), $e);
			}
		} else {
			throw new \RuntimeException('Command ' . $commandName . ' not found.');
		}
	}
}