<?php
namespace Modules\Telegram;

use Core\ModelBase;

abstract class Command {
	protected bool $hidden = false;
	protected array $data = [];
	protected Telegram $telegram;
	protected CommandRegistry $registry;
	protected ModelBase $userModel;

	public function __construct(Telegram $telegram, CommandRegistry $commandRegistry, ModelBase $userModel) {
		$this->telegram = $telegram;
		$this->registry = $commandRegistry;
		$this->userModel = $userModel;
	}

	/**
	 * Set custom attributes
	 *
	 * @param string $key
	 * @param string $value
	 */
	public function set($key, $value): void {
		$this->data[$key] = $value;
	}

	/**
	 * Get custom attributes
	 *
	 * @return mixed
	 */
	public function get($key) {
		return $this->data[$key] ?? null;
	}

	/**
	 * Get command name
	 *
	 * @return string
	 */
	abstract public function getName(): string;

	/**
	 * Get command description
	 *
	 * @return string
	 */
	abstract public function getDescription(): string;

	public function isHidden(): bool {
		return $this->hidden;
	}

	/**
	 *  Execute command
	 *
	 */
	abstract public function execute(): void;
}