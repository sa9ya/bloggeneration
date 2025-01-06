<?php
namespace Modules\Telegram;

use App\Models\TelegramUser;
use Core\Logger;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;

class Telegram extends BotApi {
	private CommandRegistry $registry;
	private $userModel;

	public function __construct($token) {
		parent::__construct($token);
		$this->registry = new CommandRegistry();
	}

	public function loadModel($userModel): void {
		$this->userModel = $userModel;
		$this->registerCommands();
	}

	/**
	 * Automatically register commands
	 */
	private function registerCommands(): void {
		$commandsPath = __DIR__ . '/commands/';
		$namespace = 'Modules\\Telegram\\Commands\\';

		foreach (glob($commandsPath . '*.php') as $file) {
			$className = $namespace . basename($file, '.php');

			if (class_exists($className) && is_subclass_of($className, Command::class)) {
				$this->registry->register(new $className($this, $this->registry, $this->userModel));
			}
		}
	}

	/**
	 * @throws Exception
	 * @throws InvalidArgumentException
	 */
	public function handleRequest(): void {
		$message = $this->userModel->message;
		$chatId = $this->userModel->chat_id;
		if ($message) {
			try {
				$this->registry->handle($message);
			} catch (\Exception $e) {
				Logger::error($e->getMessage(), $e->getTrace());
				if ($chatId) {
					$this->sendMessage($chatId, "Command not found: " . $message);
				}
			}
		}
	}
}