<?php

namespace Bitrix\Calendar\Internals\EventManager;

use Bitrix\Calendar\Core\Common;
use Bitrix\Calendar\Internals\EventManager\EventSubscriber\EventSubscriberInterface;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

abstract class BaseEvent
{
	protected static array $subscribers = [];

	/**
	 * @return EventResult[]
	 */
	public function emit(): array
	{
		foreach ($this->getSubscribers() as $subscriber)
		{
			self::$subscribers[$this::getEventType()] ??= [];

			if (!empty(self::$subscribers[$this::getEventType()][$subscriber::class]))
			{
				continue;
			}

			ServiceLocator::getInstance()->get(EventManagerInterface::class)->addEventHandler(
				fromModuleId: $this::getModuleId(),
				eventType: $this::getEventType(),
				callback: $subscriber,
			);

			self::$subscribers[$this::getEventType()][$subscriber::class] = $subscriber::class;
		}

		return $this->send($this->buildEvent());
	}

	protected function send(Event $event): array
	{
		/** @var EventManagerInterface $eventManager */
		$eventManager = ServiceLocator::getInstance()->get(EventManagerInterface::class);
		$eventManager->send($event);

		return $event->getResults();
	}

	protected function buildEvent(): Event
	{
		return new Event($this::getModuleId(), $this::getEventType(), $this->getEventParams());
	}

	public static function getModuleId(): string
	{
		return Common::CALENDAR_MODULE_ID;
	}

	abstract public static function getEventType(): string;

	abstract protected function getEventParams(): array;

	/**
	 * @return EventSubscriberInterface[]
	 */
	protected function getSubscribers(): array
	{
		return [];
	}
}
