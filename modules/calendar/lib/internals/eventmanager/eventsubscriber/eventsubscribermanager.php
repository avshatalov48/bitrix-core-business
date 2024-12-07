<?php

namespace Bitrix\Calendar\Internals\EventManager\EventSubscriber;

use Bitrix\Calendar\Internals\EventManager\BaseEvent;
use Bitrix\Calendar\Internals\EventManager\EventManagerInterface;
use Bitrix\Main\DI\ServiceLocator;

/**
 * @deprecated
 */
final class EventSubscriberManager
{
	private const DEFAULT_EVENT_SORT = 100;

	public function __invoke(): void
	{
		/** @var EventManagerInterface $eventManager */
		$eventManager = ServiceLocator::getInstance()->get(EventManagerInterface::class);

		foreach ($this->getEventSubscribers() as $eventSubscriberOptions)
		{
			if (is_array($eventSubscriberOptions))
			{
				$eventSubscriber = $eventSubscriberOptions['class'];
				$eventSort = $eventSubscriberOptions['sort'];
			}
			else
			{
				$eventSubscriber = $eventSubscriberOptions;
				$eventSort = self::DEFAULT_EVENT_SORT;
			}

			$subscriber = new $eventSubscriber();
			if (!($subscriber instanceof EventSubscriberInterface))
			{
				throw new \RuntimeException(sprintf(
					'EventSubscriber must implement EventSubscriberInterface: %s given',
					$eventSubscriber
				));
			}
			$eventClasses = $subscriber->getEventClasses();
			foreach ($eventClasses as $eventClass)
			{
				if (!is_a($eventClass, BaseEvent::class, true))
				{
					throw new \RuntimeException(sprintf('Event must extend BaseEvent: %s given', $eventClass));
				}

				$eventCallback = $subscriber;
				$eventManager->addEventHandler(
					fromModuleId: $eventClass::getModuleId(),
					eventType: $eventClass::getEventType(),
					callback: $eventCallback,
					sort: $eventSort,
				);
			}
		}
	}

	private function getEventSubscribers(): array
	{
		return [
			Event\CheckIsOpenEvent::class,
			Event\CreateEventOption::class,
			Event\SendPullAfterCreate::class,

			Event\EditEventOption::class,
			Event\AfterOpenEventEdit::class,
			Event\UpdateNewEventsNotify::class,

			EventCategory\IncrementEventsCounter::class,
			EventCategory\DecrementEventsCounter::class,
			EventCategory\UpdateChannel::class,
			EventCategory\SendPullAfterCreate::class,
			EventCategory\SendPullAfterUpdate::class,
			EventCategory\SendPullAfterDelete::class,
			['class' => Event\CreateChannelThreadForEvent::class, 'sort' => 99],
		];
	}
}
