<?php

namespace Core;

class Logger
{
	private static ?Logger $instance = null;
	private string $logDir;
	private string $logFile;

	private function __construct()
	{
		$this->logDir = __DIR__ . '/../logs/';
		$this->logFile = $this->generateLogFileName();
		$this->cleanupOldLogs();
	}

	/**
	 * @return Logger
	 */
	private static function getInstance(): Logger
	{
		if (self::$instance === null) {
			self::$instance = new Logger();
		}
		return self::$instance;
	}

	/**
	 * Generate file name with current date
	 *
	 * @return string
	 */
	private function generateLogFileName(): string
	{
		$date = date('Y-m-d');
		return $this->logDir . "log_{$date}.log";
	}

	/**
	 * Remove files which older than 7 days
	 *
	 * @return void
	 */
	private function cleanupOldLogs(): void
	{
		$files = glob($this->logDir . 'log_*.log');
		$now = time();

		foreach ($files as $file) {
			if (filemtime($file) < ($now - 7 * 24 * 60 * 60)) {
				unlink($file);
			}
		}
	}

	private function setFilePermissions(string $file): void
	{
		if (!file_exists($file)) {
			file_put_contents($file, '');
		}
		chmod($file, 0600);
	}

	/**
	 * @param string $level
	 * @param string $message
	 * @param mixed $context
	 * @return void
	 */
	private function log(string $level, string $message, $context = null): void
	{
		$date = date('Y-m-d H:i:s');
		if ($context instanceof \Exception) {
			// $data[] = $context->getTraceAsString();
			foreach ($context->getTrace() as $trace) {
				$data[] = $trace['file'] . ' ' . $trace['function'] . ':' . $trace['line'];
			}
			$data[] = $context->getTrace()[1]['args'] ?? [];
			$context = $data;
		}

		$contextString = !empty($context) ? json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '';
		$logMessage = "[$date] [$level] $message\n$contextString" . PHP_EOL;
		file_put_contents($this->logFile, $logMessage, FILE_APPEND);
		$this->setFilePermissions($this->logFile);
	}

	/**
	 * @param string $message
	 * @param mixed $context
	 * @return void
	 */
	public static function info(string $message, $context = []): void
	{
		self::getInstance()->log('INFO', $message, $context);
	}

	/**
	 * @param string $message
	 * @param mixed $context
	 * @return void
	 */
	public static function error(string $message, $context = []): void
	{
		self::getInstance()->log('ERROR', $message, $context);
	}
}