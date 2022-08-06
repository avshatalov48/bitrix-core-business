<?php

namespace Bitrix\Bizproc\Service;

use Bitrix\Main\DI\ServiceLocator;

class Manager
{
	public const SCHEDULER_SERVICE_NAME = 'schedulerService';
	public const STATE_SERVICE_NAME = 'stateService';
	public const TRACKING_SERVICE_NAME = 'trackingService';
	public const TASK_SERVICE_NAME = 'taskService';
	public const HISTORY_SERVICE_NAME = 'historyService';
	public const DOCUMENT_SERVICE_NAME = 'documentService';
	public const ANALYTICS_SERVICE_NAME = 'analyticsService';
	public const USER_SERVICE_NAME = 'userService';

	/** @var ServiceLocator */
	private $serviceLocator;

	private function __construct()
	{
		$this->serviceLocator = ServiceLocator::getInstance();
	}

	public static function getInstance(): self
	{
		return new static();
	}

	public function getService(string $serviceName)
	{
		return $this->serviceLocator->get($this->getFullServiceName($serviceName));
	}

	public function hasDebugService(string $serviceName): bool
	{
		return $this->serviceLocator->has($this->getFullServiceName($serviceName, true));
	}

	public function getDebugService(string $serviceName)
	{
		return $this->serviceLocator->get($this->getFullServiceName($serviceName, true));
	}

	/**
	 * @return string[]
	 */
	public function getAllServiceNames(): array
	{
		return [
			static::SCHEDULER_SERVICE_NAME,
			static::STATE_SERVICE_NAME,
			static::TRACKING_SERVICE_NAME,
			static::TASK_SERVICE_NAME,
			static::HISTORY_SERVICE_NAME,
			static::DOCUMENT_SERVICE_NAME,
			static::ANALYTICS_SERVICE_NAME,
			static::USER_SERVICE_NAME,
		];
	}

	public function getAllServices(): array
	{
		$services = [];

		foreach ($this->getAllServiceNames() as $serviceName)
		{
			$services[$serviceName] = $this->serviceLocator->get($this->getFullServiceName($serviceName));
		}

		return $services;
	}

	public function getAllDebugServices(): array
	{
		$services = [];

		foreach ($this->getAllServiceNames() as $serviceName)
		{
			$fullServiceName = $this->getFullServiceName($serviceName, true);

			if ($this->serviceLocator->has($fullServiceName))
			{
				$services[$serviceName] = $this->serviceLocator->get($fullServiceName);
			}
		}

		return $services;
	}

	private function getFullServiceName(string $serviceName, bool $isDebugService = false): string
	{
		if ($isDebugService)
		{
			return "bizproc.debugger.service.{$serviceName}";
		}

		return "bizproc.service.{$serviceName}";
	}
}