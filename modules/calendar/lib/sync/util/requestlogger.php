<?php

namespace Bitrix\Calendar\Sync\Util;

use Bitrix\Main;
use Bitrix\Main\Config\Option;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class RequestLogger extends Main\Diag\Logger
{
	private const OPTION_KEY = 'calendar_logger_enable';

	/**
	 * @var string
	 */
	protected string $serviceName;

	/**
	 * @var int
	 */
	protected int $userId;

	/**
	 * @var LoggerInterface|null
	 */
	private ?LoggerInterface $logger;

	/**
	 * @param int $ttl
	 * @return void
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function enable(int $ttl = 0): void
	{
		$dateEnd = 0;
		if ($ttl)
		{
			$dateEnd = (time() + $ttl);
		}

		Option::set('calendar', self::OPTION_KEY, $dateEnd, '-');
	}

	/**
	 * @return bool
	 */
	public static function isEnabled(): bool
	{
		$value = Option::get('calendar', self::OPTION_KEY, null, '-');
		if ($value === null)
		{
			return false;
		}

		$value = (int) $value;
		if (
			$value === 0
			|| $value > time()
		)
		{
			return true;
		}

		return false;
	}

	/**
	 * @param int $userId
	 * @param string $serviceName
	 */
	public function __construct(int $userId, string $serviceName)
	{
		$this->userId = $userId;
		$this->serviceName = $serviceName;
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
		$context['userId'] = $this->userId;
		$logger = $this->getLogger();
		if (is_a($logger, DatabaseLogger::class))
		{
			$logger->logToDatabase($context);
		}
		else
		{
			$logger->log(LogLevel::DEBUG, $this->prepareMessage(), $context);
		}
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
	 * @return LoggerInterface
	 */
	private function getLogger(): LoggerInterface
	{
		if (empty($this->logger))
		{
			$this->logger = $this->getDatabaseLogger();
		}
		return $this->logger;
	}

	private function getDatabaseLogger(): DatabaseLogger
	{
		return new DatabaseLogger();
	}

	protected function logMessage(string $level, string $message)
	{

	}
}
