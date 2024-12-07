<?php

namespace Bitrix\Rest\Tools\Diagnostics;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Diag;
use Psr\Log\LogLevel;

/**
 * Class LoggerManager
 * @package Bitrix\Rest\Tools\Diagnostics
 */
class LoggerManager
{
	public const TYPE_FILE = 'file';
	public const TYPE_DATABASE = 'db';
	private const AVAILABLE_TYPE = [
		self::TYPE_FILE,
		self::TYPE_DATABASE,
	];
	private const SUPPORTED_LEVEL_LIST = [
		LogLevel::EMERGENCY,
		LogLevel::ALERT,
		LogLevel::CRITICAL,
		LogLevel::ERROR,
		LogLevel::WARNING,
		LogLevel::NOTICE,
		LogLevel::INFO,
		LogLevel::DEBUG,
	];
	private const MODULE_ID = 'rest';
	private const OPTION_LEVEL = 'logger_level';
	private const OPTION_TYPE = 'logger_type';
	private const OPTION_FILE_PATH = 'logger_file_path';
	private const OPTION_LOG_FILTER = 'log_filters';
	private const OPTION_LOG_END_TIME = 'log_end_time';
	private const OPTION_SHOW_ARGUMENTS = 'logger_show_args';
	private const OPTION_SHOW_ARGUMENTS_VALUE_Y = 'Y';
	private const OPTION_SHOW_ARGUMENTS_VALUE_N = 'N';
	private static LoggerManager $instance;
	private bool $isActive;
	private string $level;
	private string $type;
	private ?Diag\Logger $logger = null;
	private bool $showArguments;
	private string $path;
	private ?array $filterOptions = null;

	private function __construct()
	{
		$this->isActive = $this->getEndTimeLogging() > time();
		$this->level = Option::get(self::MODULE_ID,self::OPTION_LEVEL, '');
		$this->type = Option::get(self::MODULE_ID,self::OPTION_TYPE, self::TYPE_FILE);
		$this->path = Option::get(self::MODULE_ID,self::OPTION_FILE_PATH, '');
		$showArguments = Option::get(self::MODULE_ID,self::OPTION_SHOW_ARGUMENTS, self::OPTION_SHOW_ARGUMENTS_VALUE_N);
		$this->showArguments = $showArguments === self::OPTION_SHOW_ARGUMENTS_VALUE_Y;
	}

	private function __clone()
	{
	}

	public static function getInstance(): LoggerManager
	{
		if (!isset(self::$instance))
		{
			self::$instance = new LoggerManager;
		}

		return self::$instance;
	}

	public function isActive(): bool
	{
		return $this->isActive;
	}

	public function deactivate(): void
	{
		$this->isActive = false;
		Option::delete(self::MODULE_ID, ['name' => self::OPTION_LOG_END_TIME]);
	}

	public function getLevel(): string
	{
		return in_array($this->level, self::SUPPORTED_LEVEL_LIST, true) ? $this->level : '';
	}

	public function setLevel($level): bool
	{
		if (in_array($level, self::SUPPORTED_LEVEL_LIST, true))
		{
			$this->level = $level;
			Option::set(self::MODULE_ID,self::OPTION_LEVEL, $this->level);

			return true;
		}

		return false;
	}

	public function getLevelList(): array
	{
		return self::SUPPORTED_LEVEL_LIST;
	}

	public function setType(string $type = self::TYPE_FILE): bool
	{
		if (in_array($type, self::AVAILABLE_TYPE, true))
		{
			$this->type = $type;
			Option::set(self::MODULE_ID,self::OPTION_TYPE, $this->type);

			return true;
		}

		return false;
	}

	public function getType(): string
	{
		return $this->type === self::TYPE_FILE ? self::TYPE_FILE : self::TYPE_DATABASE;
	}

	public function setFilePath(string $path): bool
	{
		if (!empty($path))
		{
			$this->path = $path;
			Option::set(self::MODULE_ID,self::OPTION_FILE_PATH, $this->path);

			return true;
		}

		return false;
	}

	public function getFilePath(): string
	{
		return $this->path;
	}

	public function getLogger(): ?Diag\Logger
	{
		if (is_null($this->logger) && $this->isActive() && $this->getLevel() !== '')
		{
			if ($this->getType() === self::TYPE_FILE)
			{
				if (!empty($this->getFilePath()))
				{
					$this->logger = new Diag\FileLogger($this->getFilePath());
				}
			}
			else
			{
				$this->logger = new DataBaseLogger();
			}

			if ($this->logger)
			{
				$this->logger->setLevel($this->getLevel());
				$this->logger->setFormatter(
					new LogFormatter($this->showArguments)
				);
			}
		}

		return $this->logger;
	}

	public function getFilterOptions(): array
	{
		if (!is_array($this->filterOptions))
		{
			$options = unserialize(
				Option::get(self::MODULE_ID, self::OPTION_LOG_FILTER, ''),
				[
					'allowed_classes' => false
				]
			);
			$this->filterOptions = is_array($options) ? $options : [];
		}

		return $this->filterOptions;
	}

	public function setFilterOptions(array $filters): void
	{
		$this->filterOptions = $filters;
		Option::set(self::MODULE_ID, self::OPTION_LOG_FILTER, serialize($filters));
	}

	public function getEndTimeLogging(): int
	{
		return (int)Option::get(self::MODULE_ID, self::OPTION_LOG_END_TIME, 0);
	}

	public function setEndTimeLogging(int $time): void
	{
		$this->isActive = $time > time();
		Option::set(self::MODULE_ID, self::OPTION_LOG_END_TIME, $time);
	}
}
