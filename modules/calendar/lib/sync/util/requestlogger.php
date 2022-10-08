<?php

namespace Bitrix\Calendar\Sync\Util;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Diag\FileLogger;
use Bitrix\Main;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class RequestLogger extends AbstractLogger
{
	protected const DEFAULT_LOG_FILE = "bitrix/modules/calendar_sync.log";
	protected const MAX_LOG_SIZE = 1000000;
	protected const ACCEPTED_VENDOR = ['google', 'icloud', 'office365'];
	/**
	 * @var string
	 */
	protected string $serviceName;
	/**
	 * @var int
	 */
	protected int $endTimeTimestamp;
	/**
	 * @var int
	 */
	protected int $userId;
	/**
	 * @var LoggerInterface|null
	 */
	private ?LoggerInterface $logger;

	public function __construct(int $userId, string $serviceName, LoggerInterface $logger = null)
	{
		$this->userId = $userId;
		$this->serviceName = $serviceName;
		$this->logger = $logger;
	}

	/**
	 * @param ?array $options
	 *
	 * @return FileLogger
	 */
	private function getFileLogger(?array $options = []): FileLogger
	{
		$logFile = Main\Application::getDocumentRoot()."/".self::DEFAULT_LOG_FILE;
		$maxLogSize = static::MAX_LOG_SIZE;

		return new FileLogger($logFile, $maxLogSize);
	}

	/**
	 * @return DatabaseLogger
	 */
	private function getDatabaseLogger(): DatabaseLogger
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
	 * @param array $context{
		requestParams: array,
		url: string,
		method: string,
		statusCode: string,
		response: string,
		error: string,
		host: string,
	 }
	 * @return void
	 *
	 * @throws Main\LoaderException
	 */
	public function write(array $context): void
	{
		$context['serviceName'] = $this->serviceName;
		$this->getLogger()->log(LogLevel::DEBUG, $this->prepareMessage(), $context);
	}

	private function prepareMessage(): string
	{
		return "{date} SERVICE_NAME {serviceName}
		    HOST: {host},
			REQUEST_PARAMS: {requestParams}, 
			URL: {url},
			METHOD: {method},
			STATUS_CODE: {statusCode},
			RESPONSE: {response},
			ERROR: {error}
		";
	}

	/**
	 * @param array $target
	 * @param int $duration
	 * @param array $serviceName
	 * @return void
	 * @throws Main\ArgumentOutOfRangeException
	 *
	 * if it's need to log all users - $target = ['all']
	 * else you have to send array of users ID
	 * if it's need to log all vendor - $serviceName = ['all']
	 * else you have to send array of accepted vendors
	 */
	public static function setTargetParams(array $target, int $duration = 1800, array $serviceName = ['google']): void
	{
		$endTime = time() + $duration;
		$users = [];
		$services = [];
		if ($target[0] === 'all')
		{
			$users = $target[0];
		}
		else
		{
			foreach ($target as $value)
			{
				if ((int)$value)
				{
					$users[] = (int)$value;
				}
			}
			$users = implode(',', array_unique($users));
		}

		if ($serviceName[0] === 'all')
		{
			$services = $serviceName[0];
		}
		else
		{
			foreach ($serviceName as $name)
			{
				if (in_array($name, self::ACCEPTED_VENDOR, true))
				{
					$services[] = $name;
				}
			}
			$services = implode(',', array_unique($services));
		}

		Option::set('calendar', 'calendar_sync_debug_options', "{$users}_{$endTime}_{$services}");
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
			$services = explode(',', $options[2]);

			if ($services[0] !== 'all' && !in_array($serviceName, $services, true))
			{
				return false;
			}
			if ($options[0] === 'all')
			{
				return true;
			}

			$users = explode(',', $options[0]);

			return in_array($userId, $users);
		}

		return false;
	}

	/**
	 * @param $level
	 * @param $message
	 * @param array $context{
		requestParams: array,
		url: string,
		method: string,
		statusCode: string,
		response: string,
		error: string,
		host: string,
	}
	 *
	 * @return void
	 *
	 * @throws Main\LoaderException
	 */
	public function log($level, $message, array $context = [])
	{
		$this->getLogger()->log($level, $this->prepareMessage(), $context);
	}

	/**
	 * @return LoggerInterface|null
	 *
	 * @throws Main\LoaderException
	 */
	private function getLogger(): LoggerInterface
	{
		if (empty($this->logger))
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
		return $this->logger;
	}
}
