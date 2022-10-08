<?php

namespace Bitrix\Location\Infrastructure\Service;

use Bitrix\Location\Common\BaseService;
use Bitrix\Location\Infrastructure\Service\LoggerService\ILogger;
use Bitrix\Location\Infrastructure\Service\Config\Container;

final class LoggerService extends BaseService implements ILogger
{
	/** @var static */
	protected static $instance;

	/** @var ILogger */
	private $logger;
	private $logLevel;
	private $eventsToLog;

	public function log(int $level, string $message, int $eventType = 0, array $context = [])
	{
		if(!$this->isLevelSatisfied($level) && !$this->isEventSatisfied($eventType))
		{
			return;
		}

		if(count($context) > 0)
		{
			$message = $this->interpolate($message, $context);
		}

		$this->logger->log($level, $message, $eventType, $context);
	}

	protected function isLevelSatisfied(int $level): bool
	{
		return $this->logLevel >= $level;
	}

	protected function isEventSatisfied(int $eventType): bool
	{
		return $eventType > 0 && in_array($eventType, $this->eventsToLog);
	}

	protected function __construct(Container $config)
	{
		parent::__construct($config);
		$loggerClass = $config->get('logger');
		$this->logLevel = $config->get('logLevel');
		$this->eventsToLog = $config->get('eventsToLog');
		$this->logger = new $loggerClass();
	}

	private function interpolate($message, array $context = array())
	{
		$replace = [];

		foreach ($context as $key => $val)
		{
			if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString')))
			{
				$replace['{' . $key . '}'] = $val;
			}
		}

		return strtr($message, $replace);
	}
}