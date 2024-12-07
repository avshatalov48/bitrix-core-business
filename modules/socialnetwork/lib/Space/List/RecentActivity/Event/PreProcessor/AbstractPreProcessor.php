<?php

namespace Bitrix\Socialnetwork\Space\List\RecentActivity\Event\PreProcessor;

use Bitrix\Socialnetwork\Integration\Pull\PushService;
use Bitrix\Socialnetwork\Internals\EventService\Event;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Event\Processor\ProcessorInterface;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Service;

abstract class AbstractPreProcessor implements ProcessorInterface
{
	protected Service $service;
	public function __construct(protected Event $event)
	{
		$this->service = new Service();
	}

	abstract protected function isAvailable(): bool;
	abstract protected function process(): void;
	abstract protected function getTypeId(): string;

	protected function pushEvent(array $recipients, string $eventType, array $params): void
	{
		PushService::addEvent($recipients, [
			'module_id' => PushService::MODULE_NAME,
			'command' => $eventType,
			'params' => $params,
		]);
	}
	final public function processEvent(): void
	{
		if (!$this->isAvailable())
		{
			return;
		}

		$this->process();
	}
}