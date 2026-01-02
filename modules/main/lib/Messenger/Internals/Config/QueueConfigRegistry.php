<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Internals\Config;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Config\ConfigurationException;
use Bitrix\Main\Messenger\Internals\Retry\MultiplierRetryStrategy;
use Bitrix\Main\Messenger\Internals\Retry\RetryStrategyInterface;
use Bitrix\Main\ModuleManager;

class QueueConfigRegistry
{
	/**
	 * @var array<string, QueueConfig>
	 */
	private array $queues = [];

	/**
	 * @throws ConfigurationException
	 */
	public function __construct()
	{
		$config = Configuration::getValue('messenger');

		if (!empty($config['queues']) && is_array($config['queues']))
		{
			$this->appendGlobalQueues($config['queues']);
		}

		$modules = ModuleManager::getInstalledModules();

		foreach ($modules as $moduleId => $moduleData)
		{
			$settings = Configuration::getInstance($moduleId)->get('messenger');

			if (!empty($settings['queues']) && is_array($settings['queues']))
			{
				$this->appendQueues($settings['queues'], $moduleId);
			}
		}
	}

	/**
	 * @throws ConfigurationException
	 */
	private function appendQueues(array $queues, string $moduleId): void
	{
		foreach ($queues as $queueId => $params)
		{
			$this->appendQueue($params, $queueId, $moduleId);
		}
	}

	/**
	 * @throws ConfigurationException
	 */
	private function appendGlobalQueues(array $queues): void
	{
		foreach ($queues as $queueId => $params)
		{
			$this->appendQueue($params, $queueId, $params['module'] ?? 'main');
		}
	}

	/**
	 * @throws ConfigurationException
	 */
	private function appendQueue(array $params, string $queueId, string $moduleId): void
	{
		if (empty($params['handler']))
		{
			throw new ConfigurationException(sprintf('Param "handler" should be set for the queue "%s"', $queueId));
		}

		$this->queues[$queueId] = new QueueConfig(
			$queueId,
			$params['handler'],
			$moduleId,
			$params['broker'] ?? null,
			$this->buildRetryStrategy($params)
		);
	}

	/**
	 * @return array<string, QueueConfig>
	 */
	public function getQueues(): array
	{
		return $this->queues;
	}

	public function getQueueConfig(string $queueId): ?QueueConfig
	{
		return $this->queues[$queueId] ?? null;
	}

	private function buildRetryStrategy(array $params): RetryStrategyInterface
	{
		if (!isset($params['retry_strategy']))
		{
			return new MultiplierRetryStrategy(3, 1000, 2);
		}

		$config = $params['retry_strategy'];

		return new MultiplierRetryStrategy(
			$config['max_retries'] ?? 3,
			($config['delay'] ?? 1) * 1000,
			$config['multiplier'] ?? 2,
			($config['max_delay'] ?? 0) * 1000,
			0
		);
	}
}
