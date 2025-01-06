<?php
namespace Modules\Telegram\Commands;

use App\Models\TelegramUserSettings;
use Modules\Telegram\Command;

class SettingsCommand extends Command {

	public function getName(): string
	{
		return "/settings";
	}

	public function getDescription(): string
	{
		return \App::$app->language->get('setting_command_description');
	}

	public function execute(): void
	{
	}
}