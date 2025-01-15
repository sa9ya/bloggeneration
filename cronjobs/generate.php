<?php

require_once __DIR__ . '/../core/App.php';
require_once __DIR__ . '/../vendor/autoload.php';

\App::getInstance();

$cron = new Core\CronHandler();
$cron->processRequests();