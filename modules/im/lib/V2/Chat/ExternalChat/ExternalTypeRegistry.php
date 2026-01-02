<?php

namespace Bitrix\Im\V2\Chat\ExternalChat;

use Bitrix\Im\V2\Chat\ExternalChat\Event\RegisterTypeEvent;
use Bitrix\Main\EventResult;

class ExternalTypeRegistry
{
	private static self $instance;

	/**
	 * @var array<string, Config>
	 */
	private array $registry = [];

	private function __construct()
	{
		$this->load();
	}

	public static function getInstance(): ExternalTypeRegistry
	{
		self::$instance ??= new self();

		return self::$instance;
	}

	public function getConfigs(): array
	{
		return $this->registry;
	}

	public function getConfigByType(string $type): ?Config
	{
		return $this->registry[$type] ?? null;
	}

	private function load(): void
	{
		$event = new RegisterTypeEvent();
		$event->send();

		foreach ($event->getResults() as $eventResult)
		{
			if ($eventResult->getType() === EventResult::ERROR)
			{
				continue;
			}

			$parameters = $eventResult->getParameters();
			if (!is_array($parameters))
			{
				continue;
			}

			$type = $parameters['type'] ?? null;
			$config = $parameters['config'] ?? new Config();

			if (!is_string($type) || !$type)
			{
				continue;
			}

			$this->registry[$type] = $config;
		}
	}
}