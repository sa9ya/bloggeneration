<?php

namespace Core;

class Language
{
	private static ?Language $instance = null;
	private array $translations = [];
	public string $locale;

	private function __construct(string $locale = 'en')
	{
		$this->locale = $locale;
		$this->loadTranslations();
	}

	public static function getInstance(string $locale = 'en'): Language
	{
		if (self::$instance === null || self::$instance->locale !== $locale) {
			self::$instance = new Language($locale);
		}
		return self::$instance;
	}

	private function loadTranslations(): void
	{
		$translationFile = __DIR__ . "/../config/language/{$this->locale}.php";
		if (file_exists($translationFile)) {
			$this->translations = require $translationFile;
		} else {
			$this->translations = [];
		}
	}

	public function get($name)
	{
		return $this->translations[$name] ?? $name;
	}

	public function setLocale(string $locale)
	{
		$this->locale = $locale;
	}
}