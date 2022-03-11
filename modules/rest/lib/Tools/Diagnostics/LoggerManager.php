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
	private const OPTION_ACTIVE = 'logger_active';
	private const OPTION_ACTIVE_VALUE_Y = 'Y';
	private const OPTION_ACTIVE_VALUE_N = 'N';
	private const OPTION_SHOW_ARGUMENTS = 'logger_show_args';
	private const OPTION_SHOW_ARGUMENTS_VALUE_Y = 'Y';
	private const OPTION_SHOW_ARGUMENTS_VALUE_N = 'N';

	/** @var  LoggerManager */
	private static $instance;

	private $isActive;
	private $level;
	private $type;
	private $logger;
	private $showArguments;
	private $path;

	private function __construct()
	{
		$active = Option::get(self::MODULE_ID,self::OPTION_ACTIVE, self::OPTION_ACTIVE_VALUE_N);
		$this->isActive = $active === self::OPTION_ACTIVE_VALUE_Y;
		$this->level = Option::get(self::MODULE_ID,self::OPTION_LEVEL, '');
		$this->type = Option::get(self::MODULE_ID,self::OPTION_TYPE, self::TYPE_FILE);
		$this->path = Option::get(self::MODULE_ID,self::OPTION_FILE_PATH, '');
		$showArguments = Option::get(self::MODULE_ID,self::OPTION_SHOW_ARGUMENTS, self::OPTION_SHOW_ARGUMENTS_VALUE_N);
		$this->showArguments = $showArguments === self::OPTION_SHOW_ARGUMENTS_VALUE_Y;
	}

	private function __clone()
	{
	}

	/**
	 * Returns Singleton of manager of logger
	 *
	 * @return LoggerManager
	 */
	public static function getInstance(): LoggerManager
	{
		if (self::$instance === null)
		{
			self::$instance = new LoggerManager;
		}

		return self::$instance;
	}

	/**
	 * Returns logger status
	 * @return bool
	 */
	public function isActive(): bool
	{
		return $this->isActive;
	}

	/**
	 * Sets logger status.
	 *
	 * @param bool $active
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public function setActive(bool $active = false): bool
	{
		$this->isActive = $active;
		Option::set(
			self::MODULE_ID,
			self::OPTION_ACTIVE,
			$active
				? self::OPTION_ACTIVE_VALUE_Y
				: self::OPTION_ACTIVE_VALUE_N
		);

		return true;
	}

	/**
	 * Sets printable logger arguments.
	 *
	 * @param bool $show
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public function setShowArguments(bool $show = false): bool
	{
		$this->showArguments = $show;
		Option::set(
			self::MODULE_ID,
			self::OPTION_ACTIVE,
			$this->showArguments
				? self::OPTION_SHOW_ARGUMENTS_VALUE_Y
				: self::OPTION_SHOW_ARGUMENTS_VALUE_N
		);

		return true;
	}

	/**
	 * Returns logger level.
	 *
	 * @return string
	 */
	public function getLevel(): string
	{
		return in_array($this->level, self::SUPPORTED_LEVEL_LIST, true) ? $this->level : '';
	}

	/**
	 * Sets logger level.
	 *
	 * @param $level
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
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

	/**
	 * Sets type of logger.
	 *
	 * @param string $type
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public function setType($type = self::TYPE_FILE): bool
	{
		if (in_array($type, self::AVAILABLE_TYPE, true))
		{
			$this->type = $type;
			Option::set(self::MODULE_ID,self::OPTION_TYPE, $this->type);

			return true;
		}

		return false;
	}

	/**
	 * Returns type of logger.
	 * @return string
	 */
	public function getType()
	{
		return $this->type === self::TYPE_FILE ? self::TYPE_FILE : self::TYPE_DATABASE;
	}

	/**
	 * Sets logs file path for type of logger = self::TYPE_FILE.
	 *
	 * @param string $path
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
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

	/**
	 * Returns logs file path.
	 *
	 * @return string
	 */
	public function getFilePath(): string
	{
		return $this->path;
	}

	/**
	 * Returns loggers instance.
	 *
	 * @param array $params
	 * @return Diag\Logger|null
	 */
	public function getLogger(array $params = []): ?Diag\Logger
	{
		if (!$this->logger && $this->isActive() && $this->getLevel() !== '')
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
					new Diag\LogFormatter($this->showArguments)
				);
			}
		}

		return $this->logger;
	}
}
