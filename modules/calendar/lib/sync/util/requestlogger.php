<?php

namespace Bitrix\Calendar\Sync\Util;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Diag\FileLogger;
use Bitrix\Main;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class RequestLogger
{
	protected const DEFAULT_LOG_FILE = "bitrix/modules/calendar_sync.log";
	protected const MAX_LOG_SIZE = 1000000;
	/**
	 * @var string
	 */
	protected $serviceName;
	/**
	 * @var int
	 */
	protected $endTimeTimestamp;
	/**
	 * @var int
	 */
	protected $userId;
	/**
	 * @var LoggerInterface|null
	 */
	private $logger;

	public function __construct(int $userId, string $serviceName, LoggerInterface $logger = null)
	{
		$this->userId = $userId;
		$this->serviceName = $serviceName;
		$this->logger = $logger;
	}

	/**
	 * @param array $options
	 * @return FileLogger
	 */
	private function getFileLogger(): FileLogger
	{
		$logFile = Main\Application::getDocumentRoot()."/".self::DEFAULT_LOG_FILE;
		$maxLogSize = static::MAX_LOG_SIZE;

		return new FileLogger($logFile, $maxLogSize);
	}

	private function getDatabaseLogger()
	{
		return new DatabaseLogger();
	}

	/**
	 * this method for log data from sync request
	 * you should send params in context:
	 *  - requestParams
	 *  - url
	 *  - method
	 *  - statusCode
	 *  - response
	 *  - error
	 * @param array $context
	 * @return void
	 * @throws Main\LoaderException
	 */
	public function write(array $context): void
	{
		if ($this->logger === null)
		{
			if (Main\Loader::includeModule('bitrix24'))
			{
				$this->logger = $this->getDatabaseLogger();
			}
			else
			{
				$this->logger = $this->getFileLogger();
			}
		}

		$message = "{date} HOST: {host},
			REQUEST_PARAMS: {requestParams}, 
			URL: {url},
			METHOD: {method},
			STATUS_CODE: {statusCode},
			RESPONSE: {response},
			ERROR: {error}
		";

		$this->logger->log(LogLevel::DEBUG, $message, $context);
	}

	/**
	 * @param int $target
	 * @param int $duration
	 * @param string $serviceName
	 * @return void
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function setTargetParams(int $target, int $duration = 1800, string $serviceName = 'google'): void
	{
		$endTime = time() + $duration;
		Option::set('calendar', 'calendar_sync_debug_options', "{$target}_{$endTime}_{$serviceName}");
	}

	/**
	 * @param int $userId
	 * @param string $serviceName
	 * @return bool
	 * @throws Main\ArgumentNullException
	 */
	public static function isWriteToLogForSyncRequest(int $userId, string $serviceName = ''): bool
	{
		$data = Option::get('calendar', 'calendar_sync_debug_options', false);
		if (!$data)
		{
			return false;
		}

		$options = explode("_", $data);
		if (isset($options[1]) && time() > (int)$options[1])
		{
			Option::delete('calendar', ['name' => 'calendar_sync_debug_options']);

			return false;
		}

		if (count($options) === 3)
		{
			return (int)$options[0] === $userId
				|| $serviceName === $options[2]
			;
		}

		return false;
	}
}