<?php

namespace Modules\Telegram;

use Core\ModelBase;

abstract class Command
{
	protected bool $hidden = false;
	protected array $data = [];
	protected Telegram $telegram;
	protected CommandRegistry $registry;
	protected ModelBase $userModel;
	protected array $stepArray = [];

	public function __construct(Telegram $telegram, CommandRegistry $commandRegistry, ModelBase $userModel)
	{
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
	public function set($key, $value): void
	{
		$this->data[$key] = $value;
	}

	/**
	 * Get custom attributes
	 *
	 * @return mixed
	 */
	public function get($key)
	{
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

	public function isHidden(): bool
	{
		return $this->hidden;
	}

	public function getText()
	{
		return '';
	}

	/**
	 *  Execute command
	 *
	 */
	abstract public function execute(): void;

	protected function getStep()
	{
		if (empty($this->step)) {
			$this->step = \App::$app->cache->get('step_' . $this->getUserId()) ?? 0;
		}
		return $this->step;
	}

	protected function setStep($step): void
	{
		\App::$app->cache->set('step_' . $this->getUserId(), $step);
	}

	protected function removeStep(): void
	{
		\App::$app->cache->del('step_' . $this->getUserId());
	}

	protected function getUserId(): int
	{
		return $this->userModel->user_id ?? 0;
	}

	protected function checkStep($data)
	{
		return ($this->getStep() === 0 || str_contains($data, $this->stepArray[$this->getStep()]));
	}
}